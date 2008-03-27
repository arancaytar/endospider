<?php

function page_tart_new($nation) {
  $page->title = t('Which nations should @nation endorse?', array('@nation' => $nation));
  $region = db_read('nation', array('region'), array('nation' => $nation));
  $all_nations = db_read('nation', array('nation'), array('region' => $region));
  $endorsed = db_read('endorsement', array('receiving'), array('giving' => $nation));
  
  $not_endorsed = array_diff($all_nations, $endorsed);
  
  foreach ($not_endorsed as $candidate) {
    $my_received = db_read('endorsement', array('giving'), array('receiving' => $candidate));
    $my_given = db_read('endorsement', array('receiving'), array('giving' => $candidate));
    if (!is_array($my_received)) $my_received = array($my_received);
    if (!is_array($my_given)) $my_given = array($my_given);
    $my_returned = array_intersect($my_received, $my_given);
    
    $score[$candidate] = count($my_returned) / count($my_received) * sqrt(count($my_returned)) / sqrt(count($my_given));
    $returned[$candidate] = count($my_returned);
    $received[$candidate] = count($my_received);
    $given[$candidate] = count($my_given);
  }
  
  arsort($score);
  
  foreach ($score as $candidate => $my_score) {
    $rows[] = array(
      'nation' => "<a href='http://nationstates.net/$candidate'>$candidate</a>",
      'given' => $given[$candidate],
      'received' => $received[$candidate],
      'returned' => sprintf('%.2f', $my_score * 100). '% ('. $returned[$candidate] .')',
    );
  }
  
  $header = array(
    'nation' => t('Nation'),
    'given' => t('Outgoing'),
    'received' => t('Incoming'),
    'returned' => t('Rate of Return'), 
  );
  $page->content = html_table($header, $rows);
  return $page;
}

function page_tart_remove($nation) {
  $page->title = t('Which nations should @nation withdraw their endorsement from?', array('@nation' => $nation));
}
