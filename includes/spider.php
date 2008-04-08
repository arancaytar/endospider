<?php

/*
function spider_region_meta($region_name) {
  $response = http("http://www.nationstates.net/page=display_region/region=$region_name");
  preg_match('/<p><strong>(UN|WA) Delegate:<\/strong> <a href="nation=([^"]*)">/', $response->data, $match);
  $delegate=$match[2];
  
  preg_match('/contains ([0-9,]*) nations. <span style="font-size: 8pt;">/', $response->data, $match);
  $number = str_replace(",", "", $match[1]);
  if (!$delegate || !$number) return false; 
  return array('delegate' => $delegate, 'size' => $number, 'scan_started' => date('Y-m-d H:i:s'));
}
*/

function spider_region_meta($region_name) {
  $response = http("http://www.nationstates.net/cgi-bin/regiondata.cgi/region=$region_name");
  $data = xml($response->data);
  $region['delegate'] = $data['values'][$data['index']['delegate'][0]]['value'];
  $region['size'] = $data['values'][$data['index']['numnations'][0]]['value'];
  $region['scanned'] = date('Y-m-d H:i:s');
  return $region;
}

function spider_region_un($region_name, $start) {
  $response = http("http://www.nationstates.net/page=list_nations/region=$region_name/nation=/start=$start");
  if (!preg_match('/Find a nation: <input/', $response->data)) return false;
  spider_nation_stack_();
  preg_replace('/href="nation=([^"]*)".*<img src="\/images\/(un|wa)\.gif" hspace="6" alt="(UN|WA) Member"/e', 'spider_nation_stack_("$1")', $response->data);
  return spider_nation_stack_();
}

function spider_region_nations($region_name, $start) {
  $response = http("http://www.nationstates.net/page=list_nations/region=$region_name/nation=/start=$start");
  if (!preg_match('/Find a nation: <input/', $response->data)) return false;
  spider_nation_stack_();
  preg_replace('/href="nation=([^"]*)"/e', 'spider_nation_stack_("$1")', $response->data);
  return spider_nation_stack_();
}

function spider_nation_stack_($nation = false) {
  static $un = array();
  if (!$nation) {
    $return = $un;
    $un = array();
    return $return;
  }
  $un[$nation] = $nation;
}

function spider_nation($nation_name) {
  $response = http("http://www.nationstates.net/page=display_nation/nation=$nation_name");
  if (!preg_match('/src="\/images\/smalleyelogo\.jpg"/', $response->data)) return false;
  preg_match('/region=([a-z0-9_\-]*)"/', $response->data, $match);
  
  $nation['un'] = preg_match('/_member/', $response->data);
  
  $nation['region'] = $match[1];
  
  if (preg_match('/<img class="bigflag"[^a-z]+src="\/images\/flags\/(.+)"/', $response->data, $match)) {
    $nation['flag'] = $match[1];
  }
  
  if (preg_match('/<strong>"(.*?)"<\/strong>/', $response->data, $match)) {
    $nation['motto'] = $match[1];
  }

  if (preg_match('/population of ([0-9\.]+) ((b|m)illion)/', $response->data, $match)) {
    $nation['population'] = preg_replace('/[^0-9]/', '', $match[1]) * ($match[2] == 'billion' ? 1000 : 1);
  }
  
  if (preg_match('/<p style="font-size:8pt"><strong>Most Recent Government Activity:<\/strong>[^0-9a-z]*([0-9]+) (day|hour|minute)s? ago<\/p>/', $response->data, $match)) {
    $nation['active'] = $match[1];
    switch ($match[2]) {
      case 'day':
        $nation['active'] *= 24;
      case 'hour':
        $nation['active'] *= 60;
      case 'minute':
        $nation['active'] *= 60;
    }
  }
  else $nation['active'] = 0;
  
  preg_match('/<h4>Regional Influence: <span style="font-weight: normal">(.+?)<\/span><\/h4>/', $response->data, $match);
  $nation['influence'] = $match[1];
  
  preg_match('/Endorsements Received: ([0-9]*) \((.*?)\)/', $response->data, $match);
  spider_nation_stack_();
  $out = preg_replace('/"nation=([a-z0-9_\-]*)"/e', 'spider_nation_stack_("$1")', $match[2]);
  $nation['endorsements'] = spider_nation_stack_();
  if (count($nation['endorsements']) != $match[1]) {
    status(t('This page failed a sanity check - @a nations are not @b.', 
    array('@a' => count($nation['endorsements']), '@b' => $match[1])));
  }
  return $nation;
}

function spider_nation_xml($nation_name) {
  static $map = array(
    'type' => 'type',
    'motto' => 'motto',
    'category' => 'category',
    'region' => 'region',
    'population' => 'population',
    'tax' => 'tax',
    'animal' => 'animal',
    'currency' => 'currency',
    'industry' => 'majorindustry',
    'flag' => 'flag',
  );
  
  static $freedom = array(
    'civil' => 'civilrights',
    'economy' => 'economy',
    'political' => 'politicalfreedom',
  );
  
  static $budget = array(
    'admin' => 'administration',
    'welfare' => 'welfare',
    'health' => 'healthcare',
    'education' => 'education',
    'religion' => 'spirituality',
    'military' => 'defence',
    'law' => 'lawandorder',
    'commerce' => 'commerce',
    'transport' => 'publictransport',
    'environment' => 'environment',
    'social' => 'socialequality',
  );
  
  $response = http("http://www.nationstates.net/cgi-bin/nationdata.cgi/nation=$nation_name");
  $data = xml($response->data);
  
  foreach ($map as $local => $xml) {
    $nation[$local] = $data['values'][$data['index'][$xml][0]]['value'];
  }
  
  $nation['region'] = n($nation['region'], false);
  
  foreach ($freedom as $local => $xml) {
    $nation['liberty'][$local] = $data['values'][$data['index'][$xml][0]]['value'];
  }
  
  foreach ($budget as $local => $xml) {
    $nation['budget'][$local] = str_replace('%', '', $data['values'][$data['index'][$xml][0]]['value']);
  }
  
  $nation['un'] = $data['values'][$data['index']['unstatus'][0]]['value'] != 'Non-member';
  
  $nation['active'] = time() - $data['values'][$data['index']['lastlogin'][0]]['value'];
  
  $nation['founded'] = $data['values'][$data['index']['founded'][0]]['value'];
  preg_match('/([0-9]+) (day|hour|minute)s? ago/', $nation['founded'], $match);
  $nation['founded'] = $match[1];
  switch ($match[2]) {
    case 'day':
      $nation['founded'] *= 24;
    case 'hour':
      $nation['founded'] *= 60;
    case 'minute':
      $nation['founded'] *= 60;
  }
  
  preg_match('/\/images\/flags\/(.*)$/',  $nation['flag'], $match);
  $nation['flag'] = $match[1];
  
  return $nation;
}
