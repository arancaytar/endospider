<?php

function page_() {
  return page_overview();
}

function page_overview() {
  $sql = 'SELECT `r`.`region`, `size`, COUNT(`nation`) AS `un`, `delegate`, `scan_started`, 
  UNIX_TIMESTAMP(`scan_ended`) - UNIX_TIMESTAMP(`scan_started`) AS `scan_length`
          FROM `'. DB_PREFIX .'region` `r` JOIN `'. DB_PREFIX .'nation` `n` ON `r`.`region` = `n`.`region`
          GROUP BY `r`.`region`
          ORDER BY `r`.`region`';
  $res = db_query($sql);
  while ($row = db_fetch_array($res)) $regions[] = $row;
  
  $page->title = t('Overview');
  $out = t('<p>The following regions are currently scanned.</p>');

  $out = '<table border="1">
  <tr>
    <th>Region</th>
    <th>Delegate</th>
    <th>Nations</th>
    <th>UN Nations</th>
    <th>UN %</th>
    <th>Last Scan</th>
    <th>Scan Length</th>
  </tr>
';
  foreach ($regions as $region) {
    $out .= "
    <tr>
      <td>". l('region/'. $region['region'], $region['region']) ."</td>
      <td>". l('nation/'. $region['nation'], $region['nation']) ."</td>
      <td>$region[size]</td>
      <td>$region[un]</td>
      <td>". sprintf('%3.2f', $region['un'] / $region['size'] * 100) ."</td>
      <td>$region[scan_started]</td>
      <td>$region[scan_length]</td>
    </tr>
      ";
  }
  $out .= "</table>";
  $page->content = $out;
  return $page;
}

