<?php
/************************************************************************
 * given.php															*
 * function: displays the endorsements a nation has given.				*
 * these can be filtered, showing all, returned or unreturned.			*
 ************************************************************************
 * this page is public; nothing is written to the database.				*
 ************************************************************************
 * last change: 2008-03-29 21:20										*
 *																		*
 * history:																*
 *  - 3.0 (2008-03-29) Rewritten from scratch for ES 3.0 
 *  - 2.2.2 (2006-08-05) xhtml was fixed. minor display formatting, 	*
 *					including the decimal trimming of the percentage	*
 ************************************************************************/

function page_relations($nation) {
  $outgoing = db_read(
    'endorsement', 
    array('receiving'), 
    array('giving' => $nation)
  );
  
  if (!is_array($outgoing)) $outgoing = $outgoing ? array($outgoing) : array();
  
  $incoming = db_read(
    'endorsement', 
    array('giving'), 
    array('receiving' => $nation)
  );
  if (!is_array($incoming)) $incoming = $incoming ? array($incoming) : array();
  
  $nations = db_read('nation', array('nation', 'flag', 'active'), array('nation' => array_merge($incoming, $outgoing)));
  
  foreach ($nations as $data) {
    $nations[$data['nation']] = $data;
  }
  
  $header = array(
    'nation' => t('Nation'),
    'endorsed' => t('Is endorsed'),
    'endorses' => t('Endorses'),
    'returned' => t('Mutual'),
    'flag' => t('Flag'),
    'active' => t('Last active'),
  );
  
  foreach ($outgoing as $n) {
    $rels[$n]['endorsed'] = true;
  }
  
  foreach ($incoming as $n) {
    $rels[$n]['endorses'] = true;
  }
  
  ksort($rels);
  
  foreach ($rels as $rel => $e) {
    $row[$rel] = array(
      'nation' => nl($rel),
      'endorses' => !empty($e['endorses']) ? t('Yes') : t('No'),
      'endorsed' => !empty($e['endorsed']) ? t('Yes') : t('No'),
      'returned' => (!empty($e['endorses']) && !empty($e['endorsed'])) ? t('Yes') : t('No'),
      'flag'     => flag($nations[$rel]['flag']),
      'active'   => interval($nations[$rel]['active']),
    );
  }
  
  $mutual = array_intersect($incoming, $outgoing);
  
  $page->title = t('Relations with @nation', array('@nation' => n($nation)));
  $page->content = '<p>'. t('!nation endorses %outgoing nations and is endorsed by %incoming nations. %returned of these endorsements are mutual.',
    array(
      '!nation' => nl($nation),
      '%outgoing' => count($outgoing),
      '%incoming' => count($incoming),
      '%returned' => count($mutual),
    )
  ) .'</p>';
  
  $page->content .= '<p>View <strong>all</strong>, '. l('relations/out/'. $nation, t('outgoing')) .' or '. l('relations/in/'. $nation, t('incoming')) .' endorsements only.</p>';
  $page->content .= html_table($header, $row);
  return $page;
}
 
function page_relations_out($nation) {
  $outgoing = db_read(
    'endorsement', 
    array('receiving'), 
    array('giving' => $nation)
  );
  if (!is_array($outgoing)) $outgoing = $outgoing ? array($outgoing) : array();
  $returned = db_read(
    'endorsement', 
    array('giving'), 
    array('giving' => $outgoing, 'receiving' => $nation)
  );
  if (!is_array($returned)) $returned = $returned ? array($returned) : array();
  
  $nations = db_read('nation', array('nation', 'flag', 'active'), array('nation' => $outgoing));
  
  foreach ($nations as $data) {
    $nations[$data['nation']] = $data;
  }
  
  $header = array(
    'nation' => t('Nation'),
    'returned' => t('Returned'),
    'flag' => t('Flag'),
    'active' => t('Last active'),
  );
  
  foreach ($outgoing as $out) {
    $row[$out] = array(
      'nation' => nl($out),
      'returned' => t('No'),
      'flag'     => flag($nations[$out]['flag']),
      'active'   => interval($nations[$out]['active']),
    );
  }
  
  foreach ($returned as $ret) {
    $row[$ret]['returned'] = t('Yes');
  }
  
  $page->title = t('Nations endorsed by @nation', array('@nation' => n($nation)));
    $page->content = '<p>'. t('!nation endorses %outgoing nations and is endorsed by %returned (%percent%) of these nations.',
    array(
      '!nation' => nl($nation),
      '%outgoing' => count($outgoing),
      '%returned' => count($returned),
      '%percent' => sprintf('%.2f', count($returned) * 100 / count($outgoing)),
    )
  ) .'</p>';
  $page->content .= '<p>View '. l('relations/'. $nation, 'all') .', <strong>outgoing</strong> or '. l('relations/in/'. $nation, t('incoming')) .' endorsements only.</p>';
  $page->content .= html_table($header, $row);
  return $page;
}

function page_relations_in($nation) {
  $incoming = db_read(
    'endorsement', 
    array('giving'), 
    array('receiving' => $nation)
  );
  $returned = db_read(
    'endorsement', 
    array('receiving'), 
    array('receiving' => $incoming, 'giving' => $nation)
  );
  if (!is_array($returned)) $returned = $returned ? array($returned) : array();  
  $header = array(
    'nation' => t('Nation'),
    'returned' => t('Returned'),
    'flag' => t('Flag'),
    'active' => t('Last active'),
  );
  
  $nations = db_read('nation', array('nation', 'flag', 'active'), array('nation' => $incoming ));
  
  foreach ($nations as $data) {
    $nations[$data['nation']] = $data;
  }
  
  foreach ($incoming as $in) {
    $row[$in] = array(
      'nation' => nl($in),
      'returned' => t('No'),
      'flag'     => flag($nations[$in]['flag']),
      'active'   => interval($nations[$in]['active']),
    );
  }
  
  foreach ($returned as $ret) {
    $row[$ret]['returned'] = t('Yes');
  }
  
  $page->title = t('Nations endorsing @nation', array('@nation' => n($nation)));
  $page->content = '<p>'. t('!nation is endorsed by %incoming nations and endorses %returned (%percent%) of these nations.',
    array(
      '!nation' => nl($nation),
      '%incoming' => count($incoming),
      '%returned' => count($returned),
      '%percent' => sprintf('%.2f', count($returned) * 100 / count($incoming)),
    )
  ) .'</p>';
  $page->content .= '<p>View '. l('relations/'. $nation, 'all') .', '. l('relations/out/'. $nation, t('outgoing')) .' or <strong>incoming</strong> endorsements only.</p>';
  $page->content .= html_table($header, $row);
  return $page;
}
