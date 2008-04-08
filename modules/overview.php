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

  $header = array(
    'region' => t('Region'),
    'delegate' => t('WA Delegate'),
    'size' => t('Nations'),
    'un' => t('WA Nations'),
    'unp' => t('WA %'),
    'scan_started' => t('Last scanned'),
    'scan_length' => t('Scan length'),
  );

  foreach ($regions as $region) {
    $row[] = array(
      'region' => rl($region['region']),
      'delegate' => nl($region['delegate']),
      'size' => $region['size'],
      'un' => $region['un'],
      'unp' => sprintf('%3.2f', $region['un'] / $region['size'] * 100),
      'scan_started' => $region['scan_started'],
      'scan_length' => interval($region['scan_length']),
    );
  }
  
  $out .= html_table($header, $row);
  
  $page->content = $out;
  return $page;
}

