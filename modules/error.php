<?php

function page_error_401() {
	$page->code = 401;
  $page->title = '401 Authentication Required';
  $page->content = t('You must authenticate to view the page you requested, %page. You could also try the <a href="@base">main page</a>.', 
  array('%page' => $_GET['q'], '@base' => l()));
  return $page;
}

function page_error_403() {
	$page->code = 403;
  $page->title = '403 Forbidden';
  $page->content = t('The page you requested, %page, is one you do not have access to. Please try the <a href="@base">main page</a>.', 
  array('%page' => $_GET['q'], '@base' => l()));
  return $page;
}

function page_error_404() {
	$page->code = 404;
	$page->title = '404 Not Found';
	$page->content = t('The page you requested, %page, was not found. Please try the <a href="@base">main page</a>.', 
	array('%page' => $_GET['q'], '@base' => l()));
	return $page;
}


function page_error_database() {
	$page->code = 500;
	$page->title = 'Database Unreachable';
	$page->content = t('The database server is unreachable or may not have been configured yet. If you are 
	the admin of this site, you may still need to <a href="@install">install</a> it.', array('@install' => l('install.php', TRUE)));
	return $page;
}
?>