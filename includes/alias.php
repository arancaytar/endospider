<?php
/*
 * Created on 19.05.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 
 define('CONTENT_XHTML', 'application/xhtml+xml');
 define('CONTENT_HTML', 'text/html');
 define('CONTENT_PNG', 'image/png');
 define('CONTENT_RSS', 'application/rss+xml');
 define('CONTENT_RDF', 'application/rdf+xml');
 
function execute_active_handler($path) {
 	$tokens = explode("/", $path);
	$args = array();
	
 	for ($i = 0; $i < count($tokens); $i++) {
 		$function = 'page_'. implode('_', $tokens);
		if (function_exists($function)) {
			return $tokens($args);
		}
		array_unshift($args, array_pop($tokens));
 	}
	return page_error_404($path);
}

function main() {
	ob_start('ob_gzhandler');
 	$page = execute_active_handler($_GET['q']);
	send_content_type($page->content_type);
	if (!empty($page->template)) {
		$function = 'template_'. $page->template;
		print $function($page);
	} else print $page->content;
	ob_end();
}
