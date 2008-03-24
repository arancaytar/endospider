<?php

function page_auth_login() { 
  $out = '<p>'. t('This page is restricted to administrators of the EndoSpider site. Please authenticate.') .'</p>';
  $out .= form('auth_login');
  return $out;
}

function form_auth_login() {
  $form['password'] = array(
    '#type' => 'password',
    '#title' => t('Password'),
  );
  $form['submit'] = array(
    '#type' => 'submit',
    '#value' => t('Log in'),  
  );
  return $form;
}

function form_auth_login_submit($input) {
  setcookie('endospider_admin', $input['password']);
  return $_GET['q'];
}