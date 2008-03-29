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

function page_relations_out($nation) {
  $outgoing = db_read(
    'endorsement', 
    array('receiving'), 
    array('giving' => $nation)
  );
  $returned = db_read(
    'endorsement', 
    array('giving'), 
    array('giving' => $outgoing, 'receiving' => $nation)
  );
  
  $header = array(
    'nation' => t('Nation'),
    'returned' => t('Returned'),
  );
  
  foreach ($outgoing as $out) {
    $row[$out] = array(
      'nation' => nl($out),
      'returned' => t('No'),
    );
  }
  
  foreach ($returned as $ret) {
    $row[$ret]['returned'] = t('Yes');
  }
  
  $page->title = t('Nations endorsed by @nation', array('@nation' => $nation));
  $page->content = html_table($header, $row);
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
    array('receiving' => $outgoing, 'giving' => $nation)
  );
  
  $header = array(
    'nation' => t('Nation'),
    'returned' => t('Returned'),
  );
  
  foreach ($incoming as $in) {
    $row[$in] = array(
      'nation' => nl($in),
      'returned' => t('No'),
    );
  }
  
  foreach ($returned as $ret) {
    $row[$ret]['returned'] = t('Yes');
  }
  
  $page->title = t('Nations endorsing @nation', array('@nation' => $nation));
  $page->content = html_table($header, $row);
  return $page;
}
