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
 
function alias_execute($path) {
 	$tokens = explode("/", $path);
	$args = array();
	if ($tokens[0] == '') {
	  return page_();
	}
 	while (count($tokens)) {
 		$function = 'page_'. implode('_', $tokens);
  	if (function_exists($function)) {
			return call_user_func_array($function, $args);
		}
		array_unshift($args, array_pop($tokens));
 	}
	return page_error_404($path);
}
