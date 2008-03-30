<?php

function page_gather() {
	if (!auth()) return page_auth_login();
  
  $page->title = t('Gathering data');
  
  $page->content = "<p>The region entered will be scanned completely and saved in the database. The name must be entered in raw format, ie. 'my_region' rather than 'My Region'.
  <strong>Caution:</strong> The scanner's database model is static and can only save one state of a region. Before beginning the scan, all previous data will be discarded irreversibly.</p>";
  
  $page->content .= form('gather');
  $page->content .= '
  <link rel="stylesheet" type="text/css" href="style/status.css" />
  <script type="text/javascript" src="style/scripts/status.js" />
  <div id="status-wrapper">
    <div id="status-progress">
      <div id="status-progress-done"></div>
    </div>
    <div id="status-description">
      Time passed: <span id="status-time-passed">00:00</span>, expected time remaining: <span id="status-time-remaining">00:00</span>. 
    </div>
  </div>';
  return $page;
} 

function page_gather_status() {
  $page->content_type = 'application/json';
  $status = array('done' => $_SESSION['done'], 'time' => interval($_SESSION['time']));
  $page->template = 'none';
  $page->content = json($status);
  return $page;  
}

function interval($time) {
  $seconds = $time % 60;
  $time = ($time - $seconds) / 60;
  $minutes = $time % 60;
  $time = ($time - $minutes) / 60;
  $hours = $time;
  $out = array();
  if ($hours) $out[] = "$hours hour". ($hours > 1) ? 's' : '';
  if ($minutes) $out[] = "$minutes minute". ($minutes > 1) ? 's' : '';
  if ($seconds) $out[] = "$seconds second". ($second > 1) ? 's' : '';
  return implode(" ", $out);
}

function form_gather() {
  $form['region'] = array(
    '#type' => 'text',
    '#title' => t('Region'),
  );
  $form['ajax'] = array(
    '#type' => 'hidden',
    '#value' => 0,
    
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Index'),
  );
  return $form;
}

function form_gather_submit($values) {
  if (!$values['ajax']) {
    header('Content-type: text/plain');    
  }
  $_SESSION['ajax'] = $values['ajax'];

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
      db_write('nation', $nations, array('region' => $region, 'indexed' => date('Y-m-d H:i:s')), DB_REPLACE);
    }
    $remaining = gather_time_remaining_1($meta['size'], $i, $un, $running / $requests);
    status(t('    Projected time remaining: '. $remaining));
    progress(0.5 * $i / $meta['size'], $remaining); 
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
      db_write('nation', $nation, array('received' => count($nation_data['endorsements']), 'active' => $nation_data['active'], 'scanned' => date('Y-m-d H:i:s'), 'flag' => $nation_data['flag']), DB_UPDATE);
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
    $remaining = gather_time_remaining_2(count($nations), $i, $running / $requests);
    status(t('    Projected time remaining: '. $remaining));
    progress(0.5 + 0.5 * $i / count($nations), $remaining);
  }
  
  status(t('Saving the given counts.'));
  db_query('CREATE TEMPORARY TABLE ngiven SELECT giving AS nation, COUNT(*) AS outgoing FROM {endorsement} GROUP BY giving');
  db_query('UPDATE {nation} NATURAL JOIN ngiven SET given = outgoing');
  status(t('Done with scan.'));
  progress(1, 0);
  db_write('region', $region, array('scan_ended' => date('Y-m-d H:i:s')), DB_UPDATE);
  exit;
}

function gather_time_remaining_1($size, $progress, $un, $avg) {
  //print "$un nations in $progress out of $size : ";
  $un = $un / ($progress + 1) * ($size + 1);
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

