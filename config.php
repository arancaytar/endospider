<?php

define('MYSQL_HOST', '');
define('MYSQL_USER', '');
define('MYSQL_PASS', '');
define('MYSQL_DATABASE', '');
define('DB_PREFIX', '');

$passwords = array(
  '<password1>' => array('view' => TRUE, 'gather' => TRUE),
  '<password2>' => array('view' => TRUE),
  '<password3>' => array('view' => array('<region>'), 'gather' => array('<region>'))
);
