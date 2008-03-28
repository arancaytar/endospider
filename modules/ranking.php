<?php

function page_ranking($sort, $region, $r = FALSE) {
  $orders = array('given' => 'given', 'received' => 'received', 'name' => 'nation');
  $order = $orders[$sort] .= (!empty($r) == ($sort!='name')) ? ' ASC' : ' DESC';
  $result = db_query('SELECT nation, given, received FROM {nation} WHERE region="%s" ORDER BY '. $order, $region);
  while ($row = db_fetch_array($result)) $rows[] = $row;
  $header = array(
    'nation' => array(
      'data' => l('ranking/name'. ($sort == 'name' && !$r) ? '/r' : '', t('Nation')), 
      'class' => ($sort == 'name' ? 'sorted-' . ($r ? 'down' : 'up') : 'sort-up'),
    ),  
    'given' => array(
      'data' => l('ranking/given'. ($sort == 'given' && !$r) ? '/r' : '', t('Given')),
      'class' => ($sort == 'given' ? 'sorted-' . ($r ? 'up' : 'down') : 'sort-down'),
    ),
    'received' => array(
      'data' => l('ranking/received'. ($sort == 'received' && !$r) ? '/r' : '', t('Received')),
      'class' => ($sort == 'received' ? 'sorted-' . ($r ? 'up' : 'down') : 'sort-down'),
    ),
  );
  $page->title = t('Ranking nations in @region', array('@region'), $region);
  $page->content = html_table($header, $rows);
  return $page;
}
