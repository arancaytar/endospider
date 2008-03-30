<?php

function page_region($region) {
  $page->title = t('Region: @region', array('@region' => n($region)));
  $r = db_read('region', array('size', 'delegate'), array('region' => $region));
  
  $un = db_fetch_array(db_query('SELECT COUNT(*) FROM {nation} WHERE `region` = "%s"', $region));
  $endo = db_fetch_array(db_query('SELECT COUNT(*) FROM {endorsement} WHERE `region` = "%s"', $region));
  $delegate = db_read('nation', array('received'), array('nation' => $r['delegate']));
  $vice = db_fetch_array(db_query('SELECT `received` FROM {nation} WHERE `region` = "%s" ORDER BY `received` DESC LIMIT 1,1', $region));
  
  $page->content =  '<p>'. t('This region contains %size nations and is ruled by UN delegate !delegate', array('%size' => $r['size'], '!delegate' => nl($r['delegate']))) .'</p>';
  
  $page->content .= t('
<h3>Statistical overview</h3>
<ul>
  <li><strong>UN Activity</strong>: %un% UN membership</li>
  <li><strong>Endorsement saturation</strong>: %endo% of possible endorsements exchanged</li>
  <li><strong>Invasion security</strong>: %security% of nations endorse delegate</li>
  <li><strong>Delegate power</strong>: %power% difference to delegate-in-waiting</li>
</ul>', array(
    '%un' => sprintf('%.2f', 100 * $un / $r['size']),
    '%endo' => sprintf('%.2f', 100 * $endo / $un / ($un - 1)),
    '%security' => sprintf('%.2f', 100 * $delegate / $un),
    '%power' => sprintf('%.2f', 100 * ($delegate - $vice) / $delegate),
  ));
  
  $res = db_query('SELECT `nation`, `received`, COUNT(*) AS `given` FROM {nation} `n` JOIN {endorsement} `e` ON `nation` = `giving` 
                    WHERE `n`.`region` = "%s" GROUP BY `nation` ORDER BY `received` DESC LIMIT 0, 20', $region);
  
  $header = array('rank' => t('#'), 'nation' => t('Nation'), 'received' => t('Received'), 'given' => t('Given'));
  
  $i = $j = $last = 1;
  while ($row = db_fetch_array($res)) {
    $row['rank'] = $i++ && ($last != $row['received']) ? $i : $j;
    $last = $row['received'];
    $j = $row['rank'];
    $row['nation'] = nl($row['nation']);
    $received[] = $row;
  }
  
  $res = db_query('SELECT `nation`, `received`, COUNT(*) AS `given` FROM {nation} `n` JOIN {endorsement} `e` ON `nation` = `giving` 
                    WHERE `n`.`region` = "%s" GROUP BY `nation` ORDER BY `given` DESC LIMIT 0, 20', $region);

  $i = $j = $last = 1;
  while ($row = db_fetch_array($res)) {
    $row['rank'] = $i++ && ($last != $row['received']) ? $i : $j;
    $last = $row['received'];
    $j = $row['rank'];
    $row['nation'] = nl($row['nation']);
    $given[] = $row;
  }
  
  $page->content .= '<h3>'. t('Top twenty powers') .'</h3>';
  $page->content .= '<p>'. l('ranking/received/'. $region, t(n($region))). '</p>';
  $page->content .= html_table($header, $received);
  $page->content .= '<h3>'. t('Top twenty tarters') .'</h3>';
  $page->content .= '<p>'. l('ranking/given/'. $region, t(n($region))). '</p>';
  $page->content .= html_table($header, $given);

  return $page;
}
