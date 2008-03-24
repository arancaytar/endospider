<?php
 
/* this is the curl module for endospider. It gets a certain URL, replacing file_get_contents 
 */
 
 function curl_get($url) {
 	$user_agent=user_agent();
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_USERAGENT, $user_agent);
	// set the target url
	curl_setopt($ch, CURLOPT_URL,$url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_TIMEOUT, 30);
	$result= curl_exec ($ch);
	curl_close ($ch); 
	$result=explode("\n",$result);
	foreach ($result as $i=>$line) $result[$i]="$line\n";
	return $result;
 }
  function user_agent() {
 # this will later get info from config.php - in version 2.4
 # This is all preliminary, emergency update.
 	$user='Ermarian';
	$site='embassy.ermarian.net';
	$version = '2.4pre-alpha';
	$rv = 10;
	// User Agent: EndoSpider/<VERSION> rv:<RV> by ermarian.net (Used by: <USER> on <SITE>)
	$user_agent="EndoSpider/$version rv:$rv by ermarian.net (Used by: $user on $site)";
	return $user_agent;
 }
?>