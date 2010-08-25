<?php

function page_auth_login($role) { 
  $out = '<p>'. t('This page is restricted to %role of the EndoSpider site. Please authenticate.', array('%role' => $role)) .'</p>';
  $out .= form('auth_login');
  return $out;
}

function page_logout() {
  setcookie('endospider_login', '', -1);
  header('HTTP/1.1 303 See Other');
  header('Location: ' . url(''));
  exit;
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
  setcookie('endospider_login', $input['password']);
  return isset($_GET['q']) ? $_GET['q'] : '';
}
