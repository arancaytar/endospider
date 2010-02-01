<?php

function page_tart_new($nation) {
  $page->title = t('Which nations should @nation endorse?', array('@nation' => n($nation)));
  $region = db_read('nation', array('region'), array('nation' => $nation));
  $all_nations = db_read('nation', array('nation', 'active', 'flag'), array('region' => $region));
  foreach ($all_nations as $i => $n) {
    $data[$n['nation']] = $n;
    $all_nations[$i] = $n['nation'];
  }
  
  $endorsed = db_read('endorsement', array('receiving'), array('giving' => $nation));
  $endorsing = db_read('endorsement', array('giving'), array('receiving' => $nation));
  
  $all_nations = array_diff($all_nations, array($nation));
  $not_endorsed = array_diff($all_nations, $endorsed);
  $not_endorsing = array_diff($all_nations, $endorsing);
  $not_endorsed = array_intersect($not_endorsed, $not_endorsing);
  
  foreach ($not_endorsed as $candidate) {
    $my_received = db_read('endorsement', array('giving'), array('receiving' => $candidate));
    $my_given = db_read('endorsement', array('receiving'), array('giving' => $candidate));
    if (!is_array($my_received)) $my_received = $my_received ? array($my_received) : array();
    if (!is_array($my_given)) $my_given =  $my_given ? array($my_given) : array();
    $my_returned = array_intersect($my_received, $my_given);

    $returned[$candidate] = count($my_returned);
    $received[$candidate] = count($my_received);
    $given[$candidate] = count($my_given);
    $active = max(0, (28 * 86400 - $data[$candidate]['active']) / 28 / 86400);
    $score[$candidate] = ($returned[$candidate]+1) / ($received[$candidate]+1) * sqrt(($returned[$candidate]+1) / ($given[$candidate]+1)) * sqrt($active);
  }
  
  arsort($score);
  
  foreach ($score as $candidate => $my_score) {
    $rows[] = array(
      'nation' => nl($candidate),
      'given' => $given[$candidate],
      'received' => $received[$candidate],
      'returned' => sprintf('%.2f', $my_score * 100). '% ('. $returned[$candidate] .')',
      'flag' => flag($data[$candidate]['flag']),
      'active' => interval($data[$candidate]['active']) .' ago',
    );
  }
  
  $header = array(
    'nation' => t('Nation'),
    'given' => t('Outgoing'),
    'received' => t('Incoming'),
    'returned' => t('Rate of Return'), 
    'flag' => t('Flag'),
    'active' => t('Active'),
  );
  $page->content = '<p>'. t("This view takes into account the various nations' endorsement behavior and activity. The highest-ranked nations" .
  " are those that endorse everyone who endorses them, and noone else, and that were active very recently. Nations that hand out many endorsements 
  that are not returned (tarters) are penalized as their uncommonly high RoR falsely implies they pay attention to incoming endorsements when it actually has 
  little influence on their tarting behavior. In fact, endorsing them might make them pass you by - they only endorse people who have not yet endorsed them. 
  Inactive nations are penalized for obvious reasons.") .'</p>';
  $page->content .= html_table($header, $rows);
  return $page;
}

function page_tart_remove($nation) {
  $page->title = t('Which nations should @nation withdraw their endorsement from?', array('@nation' => $nation));
}

function page_tart_nudge($nation) {
  $given = db_read('endorsement', array('receiving'), array('giving' => $nation));
  $returned = db_read('endorsement', array('giving'), array('receiving' => $nation, 'giving' => $given));
  
  $unreturned = array_diff($given, $returned);

  $all_nations = db_read('nation', array('nation', 'active', 'flag'), array('nation' => $unreturned));
  foreach ($all_nations as $i => $n) {
    $data[$n['nation']] = $n;
  }
  
  foreach ($unreturned as $candidate) {
    $my_received = db_read('endorsement', array('giving'), array('receiving' => $candidate));
    $my_given = db_read('endorsement', array('receiving'), array('giving' => $candidate));
    if (!is_array($my_received)) $my_received = $my_received ? array($my_received) : array();
    if (!is_array($my_given)) $my_given =  $my_given ? array($my_given) : array();
    $my_returned = array_intersect($my_received, $my_given);

    $returned[$candidate] = count($my_returned);
    $received[$candidate] = count($my_received);
    $given[$candidate] = count($my_given);
    $active = max(0, (28 * 86400 - $data[$candidate]['active']) / 28 / 86400);
    $score[$candidate] = ($returned[$candidate]+1) / ($received[$candidate]+1) * sqrt(($returned[$candidate]+1) / ($given[$candidate]+1)) * sqrt($active);
  }
  
  $header = array(
    'nation' => t('Nation'),
    'given' => t('Outgoing'),
    'received' => t('Incoming'),
    'returned' => t('Rate of Return'), 
    'flag' => t('Flag'),
    'active' => t('Active'),
  );
  
  arsort($score);
  
  foreach ($score as $candidate => $my_score) {
    $rows[] = array(
      'nation' => nl($candidate),
      'given' => $given[$candidate],
      'received' => $received[$candidate],
      'returned' => sprintf('%.2f', $my_score * 100). '% ('. $returned[$candidate] .')',
      'flag' => flag($data[$candidate]['flag']),
      'active' => interval($data[$candidate]['active']) .' ago',
    );
  }
  
  
  $page->title = t('Which nations should @nation nudge for an endorsement?', array('@nation' => n($nation)));
  $page->content = '<p>'. t("Some nations just plainly didn't notice you endorsed them. Besides sending a telegram, you can try 'cycling' your endorsement. 
    This will bump you up in the events list and possibly cause them to reciprocate this time. Be careful not to do this excessively because you don't win endorsements by 
    annoying people.") .'</p>';
  
  $page->content .= '<p>'. t("This list is weighted like the normal tarting list, by the score these nations would have if you hadn't endorsed them yet. The ones most likely to
    respond to tarting are at the top.") .'</p>';
  $page->content .= html_table($header, $rows);
  return $page;
}
