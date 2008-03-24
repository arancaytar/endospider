<?php

function display_to_url($name) {
	global $debug;
	if ($debug) echo "$name=>";
	$name=strtolower($name);
	$name=str_replace(" ","_",$name);
	if ($debug) echo "$name";	
	return $name;
}
function url_to_display($name) {
	global $debug;
	if ($debug) echo "$name=>";
	$name=str_replace("_"," ",$name);
	$name=explode(" ",$name);
	for ($i=0;$i<sizeof($name);$i++) $name[$i]=ucfirst($name[$i]);
	$name=implode(" ",$name);
	if ($debug) echo "$name";
	return $name;
}

?>