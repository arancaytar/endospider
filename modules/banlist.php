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
  $nations = explode("\n", $values['nations']);
  foreach ($nations as $nation) {
    $data = spider_nation($nation);
    print "$nation\t". ($data['un'] ? "UN" : "NO") ."\t". $data['region'] ."\t". count($data['endorsements']) ."\n";
  }
}
