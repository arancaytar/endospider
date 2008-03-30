<?php

function page_banlist() {
  $page->title = 'Checking list of nations';
  $page->content = form('banlist');
  return $page;
}

function form_banlist() {
  $form['nations'] = array(
    '#type' => 'textarea',
    '#title' => t('Nations (one per line)'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Scan'),
  );
  return $form;
}

function form_banlist_submit($values) {
  header("Content-type: text/plain");
  print "#\tNation\tUN?\tRegion\tEndorsements";
  $nations = explode("\n", $values['nations']);
  foreach ($nations as $i=>$nation) {
    $nation = trim($nation);
    $data = spider_nation($nation);
    print "$i\t$nation\t". ($data['un'] ? "UN" : "-") ."\t". $data['region'] ."\t". count($data['endorsements']) ."\n";
  }
}
