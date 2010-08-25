<?php

function auth($perm = NULL, $region = NULL) {
  global $passwords;

  if (isset($_COOKIE['endospider_login'])) $cookie = $_COOKIE['endospider_login'];
  else return FALSE;

  if (isset($passwords[$cookie])) $active = $passwords[$cookie];
  else return FALSE;

  if ($perm) {
    if (isset($active[$perm])) {
      if ($active[$perm] === TRUE) return TRUE;
      elseif ($region && in_array($region, $active[$perm])) return TRUE;
    }
    else return FALSE;
  }
  else return TRUE;
  /*
  && md5($passwords[$region][$perm]) == $cookie) return TRUE;

	if (isset($passwords['*'][$perm]) && md5($passwords['*'][$perm]) == $cookie) return TRUE;

  if (!$perm) {
    foreach ($passwords as $r) foreach ($r as $p) if (md5($p) == $cookie) return TRUE;
  }

	if (!empty($_COOKIE['endospider_login'])) {
	  //message(t('The password you entered is wrong.'));
	}
  return false; */
}
