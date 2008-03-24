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
  db_write('region', $region, $meta);
  status(t('Now indexing UN nations in region...'));
  for ($i = 0; $i < $meta['size']; $i += 15) {
    status(t('  Downloading list of nations from !start to !end', array('!start' => $i, '!end' => $i + 14)));
    $nations = spider_region_un($region, $i);
    if (count($nations)) {
      status(t('    Found !un UN nations: ', array('!un' => count($nations))) . implode(', ', $nations));
      status(t('    Writing nations to database...'));      
    }
    db_write('nation', $nations, array('region' => $region), DB_REPLACE);
  }
  status(t('Done with indexing.'));
}

function gather_scan($region) {
  status(t("Beginning scan of $region, stage 2 of 2..."));
  status(t("Retrieving UN nations from database..."));
  $nations = db_read('nation', array('region' => $region));
  
  status(t("There are ". count($nations) . " UN nations in this region..."));
  status(t('Launching deep scan.'));
  foreach ($nations as $nation) {
    status(t('  Downloading spotlight page of '. $nation));
    $nation_data = spider_nation($nation);
    if ($nation_data['region'] == $region) {
      status(t('    Writing '. count($nation_data['endorsements']) .' to database...'));
      db_write('nation', $nation, $nation_data['endorsements'], DB_UPDATE);
      $endorsements = array();
      foreach ($nation_data['endorsements'] as $giver) {
        $endorsements[] = array('giving' => $giver, 'receiving' => $nation);
      }
      db_write('endorsement', $endorsements, array('region' => $region), DB_REPLACE);
    } else {
      status('    Nation has left region, deleting from database...');
      db_write('nation', $nation, NULL, DB_DELETE);
    }
  }
  status(t('Done with scan.'));
  exit;
}
