<?php
function dbconnect() {
	include("config.php");
	$link = @mysql_connect($host,$user,$password);
	@mysql_select_db($database,$link);
	if (!$link) {
		error("No connection to the database could be established. Note: You must run the 
		<a href='install.php'>installer</a> before using this application.");
	}
	return $link;
}
?>