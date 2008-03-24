<?php

function spider_region_meta($region_name) {
  $response = http("http://www.nationstates.net/page=display_region/region=$region_name");
  preg_match('/<p><strong>UN Delegate:<\/strong> <a href="nation=([^"]*)">/', $response->data, $match);
  $delegate=$match[1];
  
  preg_match('/contains ([0-9,]*) nations. <span style="font-size: 8pt;">/', $response->data, $match);
  $number = str_replace(",", "", $match[1]);
  if (!$delegate || !$number) return false; 
  return array('delegate' => $delegate, 'size' => $number, 'scan_started' => date('Y-m-d H:i:s'));
}

function spider_region_un($region_name, $start) {
  $response = http("http://www.nationstates.net/page=list_nations/region=$region_name/nation=/start=$start");
  if (!preg_match('/Find a nation: <input/', $response->data)) return false;
  spider_nation_stack_();
  preg_replace('/href="nation=([^"]*)".*<img src="\/images\/un\.gif" hspace="6" alt="UN Member"/e', 'spider_nation_stack_("$1")', $response->data);
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
  $nation['region'] = $match[1];
  preg_match('/Endorsements Received: ([0-9]*) \((.*?)\)/', $response->data, $match);
  spider_nation_stack_();
  preg_replace('/nation=([a-z0-9_\-]*)/', 'spider_nation_stack_("$1")', $match[2]);
  $nation['endorsements'] = spider_nation_stack_();
  if (count($nation['endorsements']) != $match[1]) {
    status(t('This page failed a sanity check - @a nations are not @b.', 
    array('@a' => count($nation['endorsements']), '@b' => $match[1])));
    return false;
  }
  return $nation;
}
