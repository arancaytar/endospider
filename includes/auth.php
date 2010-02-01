<?php

function auth() {
	if (isset($_COOKIE['endospider_admin']) && $_COOKIE['endospider_admin'] == AUTH_ADMIN_PASS) return true;
	if (!empty($_COOKIE['endospider_admin'])) {
	  message(t('The password you entered is wrong.'));
	}
  return false; 
}
