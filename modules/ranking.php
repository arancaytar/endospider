<?php

function page_ranking($sort, $region, $r = FALSE) {
  $orders = array('given' => 'given', 'received' => 'received', 'name' => 'nation');
  $order = $orders[$sort] .= (!empty($r) == ($sort!='name')) ? ' ASC' : ' DESC';
  $result = db_query('SELECT nation, given, received, flag, influence FROM {nation} WHERE region="%s" ORDER BY '. $order, $region);
  $i = $j = $last = 0;
  while ($row = db_fetch_array($result)) {
    $row['rank'] = ++$i && ($last != $row['received']) ? $i : $j;
    $last = $row['received'];
    $j = $row['rank'];
    $row['nation'] = nl($row['nation']);
    $row['flag'] = flag($row['flag']);
    $rows[] = $row;
  }
  $header = array(
    'rank' => t('#'),
    'nation' => array(
      'data' => l('ranking/name/'. $region . ($sort == 'name' && !$r ? '/r' : ''), t('Nation')), 
      'class' => ($sort == 'name' ? 'sorted-' . ($r ? 'down' : 'up') : 'sort-up'),
    ),  
    'given' => array(
      'data' => l('ranking/given/'. $region . ($sort == 'given' && !$r ? '/r' : ''), t('Given')),
      'class' => ($sort == 'given' ? 'sorted-' . ($r ? 'up' : 'down') : 'sort-down'),
    ),
    'received' => array(
      'data' => l('ranking/received/'. $region . ($sort == 'received' && !$r ? '/r' : ''), t('Received')),
      'class' => ($sort == 'received' ? 'sorted-' . ($r ? 'up' : 'down') : 'sort-down'),
    ),
    'flag' => array(
      'data' => t('Flag'),
    ),
    'influence' => t('Influence'),
  );
  $page->title = t('Ranking nations in @region', array('@region' => n($region)));
  $page->content = html_table($header, $rows);
  return $page;
}
