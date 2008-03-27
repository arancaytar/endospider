<?php

function page_gather() {
	if (!auth()) return page_auth_login();
  
  return form('gather');
} 

function form_gather() {
  $form['region'] = array(
    '#type' => 'text',
    '#title' => t('Region'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Index'),
  );
  return $form;
}

function form_gather_submit($values) {
  header('Content-type: text/plain');
  $region = $values['region'];
  db_query('DELETE FROM {region} WHERE region="%s"', $region);
  db_query('DELETE FROM {nation} WHERE region="%s"', $region);
  db_query('DELETE FROM {endorsement} WHERE region="%s"', $region);
  gather_index($region);
  gather_scan($region);
}

function gather_index($region) {
  status(t('Beginning scan of @region, stage 1 of 2...', array('@region' => $region)));
  status(t('Finding total number of nations...'));
  do {
    if (!$meta = spider_region_meta($region)) status(t('ERROR, Redialing...'));
  } while (!$meta);
  
  status(t('There are @n nations in region; @del is delegate.',
    array('@n' => $meta['size'], '@del' => $meta['delegate'])));
  status(t('Writing meta info to database...'));
  db_write('region', $region, $meta, DB_REPLACE);
  status(t('Now indexing UN nations in region...'));
  $un = 0;
  for ($i = 0; $i < $meta['size']; $i += 15) {
    status(t('  Downloading list of nations from !start to !end', array('!start' => $i, '!end' => $i + 14)));
    do {
      $nations = spider_region_un($region, $i);
      if (!is_array($nations)) status(t('ERROR, Redialing...'));
    } while (!is_array($nations));
    $un += count($nations);
    $requests++;
    $running = status(t('  Downloaded.'));
    if (count($nations)) {
      status(t('    Found !un UN nations: ', array('!un' => count($nations))) . implode(', ', $nations));
      status(t('    Writing nations to database...'));
      db_write('nation', $nations, array('region' => $region), DB_REPLACE);
    }
    status(t('    Projected time remaining: '. gather_time_remaining_1($meta['size'], $i, $un, $running / $requests)));
  }
  status(t('Done with indexing.'));
}

function gather_scan($region) {
  $start = status(t("Beginning scan of $region, stage 2 of 2..."));
  status(t("Retrieving UN nations from database..."));
  $nations = db_read('nation', array('nation'), array('region' => $region));
  $requests = 0;
  status(t("There are ". count($nations) . " UN nations in this region..."));
  status(t('Launching deep scan.'));
  foreach ($nations as $i => $nation) {
    status(t('  Downloading spotlight page of '. $nation));
    do {
      if (!$nation_data = spider_nation($nation)) status(t('ERROR, Redialing...'));
      $requests++;
    } while (!$nation_data);
    
    if ($nation_data['region'] == $region) {
      status(t('    Writing '. count($nation_data['endorsements']) .' to database...'));
      db_write('nation', $nation, array('received' => count($nation_data['endorsements']), 'active' => $nation_data['active']), DB_UPDATE);
      $endorsements = array();
      foreach ($nation_data['endorsements'] as $giver) {
        $endorsements[] = array('giving' => $giver, 'receiving' => $nation);
      }
      db_write('endorsement', $endorsements, array('region' => $region), DB_REPLACE);
    } else {
      status('    Nation has left region, deleting from database...');
      db_write('nation', $nation, NULL, DB_DELETE);
    }
    $running = status('    Done with nation.') - $start;
    status(t('    Projected time remaining: '. gather_time_remaining_2(count($nations), $i, $running / $requests)));
  }
  
  status(t('Saving the given counts.'));
  db_query('CREATE TEMPORARY TABLE ngiven SELECT nation, COUNT(*) AS out {nation} n JOIN {endorsement} e ON nation = giving GROUP BY nation');
  db_query('UPDATE {nation} NATURAL JOIN {ngiven} SET given = out');
  status(t('Done with scan.'));
  exit;
}

function gather_time_remaining_1($size, $progress, $un, $avg) {
  //print "$un nations in $progress out of $size : ";
  $un = $un / $progress * $size;
  //print "$un projected. ";
  //print "$avg time per request * ". (($size - $progress)/15) ." requests to go: "; 
  $time = $avg * ($size - $progress) / 15;
  //print "$time for indexing. ";
  $time += $un * $avg;
  //print "$un * $avg for scanning.";
  return $time;
}

function gather_time_remaining_2($un, $progress, $avg) {
  $time = ($un - $progress) * $avg;
  return $time;
}

