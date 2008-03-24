<?php
//header("Content-type: text/plain");
include("config.php");
include("includes/all.php");
if ($_COOKIE['endo']!=$admin) {
	$msg = "You must be logged in to perform this action. Log in with the admin password on <a href='gather.php'>this page</a>.";
	include("error.php");
	exit;
}
foreach ($_GET as $name=>$value) $$name=$value;
$region=display_to_url($region);
$region_show=url_to_display($region);
if ($region) $link=@dbconnect();
function index_region($region_name,$start) {
	global $prefix;
	echo "Finding total number of nations in $region_name...";
	flush();
	$meta = get_region_meta($region_name);
	echo "done:<br/>\n";
	$number=$meta['number'];
	$delegate=$meta['delegate'];
	echo "There are $number nations.<br/>\n";
	echo "$delegate is delegate.<br/>\n";
	write_meta_db($region_name,$meta);
	echo "Preparing to find UN nations:<br/>\n";
	flush();
	if (!$start) $start=0;
	for ($i=$start;$i<$number;$i+=15) {
		echo "Downloading list of nations $i to ".($i+15) ."...";
		flush();
		$data_parse = curl_get("http://www.nationstates.net/page=list_nations/region=$region_name/nation=/start=$i");
			for ($j=0;$j<sizeof($data_parse);$j++) {
				if (preg_match('/<p>Find a nation: <input type="text" size="30" name="nation_name" value="">/',
								$data_parse[$j])) $data=$data_parse;
			}
		while (!$data) {
			echo "<br/>\n<strong>ERROR! Redialing...</strong>"; 
			$data_parse = curl_get("http://www.nationstates.net/page=list_nations/region=$region_name/nation=/start=$i");
			//var_dump($data);
			$preg='/<p>Find a nation: <input type="text" size="30" name="nation_name" value="">/';
			for ($j=0;$i<sizeof($data_parse);$j++) {
				//echo $data_parse[$i];
				//echo $preg;
				if (preg_match($preg,$data_parse[$j])) $data=$data_parse;
			}
			flush();
		}
		echo "done.<br/>\n";
		flush();
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Finding UN members in list...";
		$un_nations = get_un_nations($data);
		echo "done.<br/>\n";
		//var_dump($un_nations);
		flush();
		if ($un_nations) write_to_db($un_nations,$region_name,$i+15);
		flush();
		$sql = "update ".$prefix."regions set scanned=$i+15 where region='$region_name';";
		//echo $sql;
		mysql_query($sql);
	}	
}



function get_un_nations($lines) {
	for ($i=0;$i<sizeof($lines);$i++) {
			if (preg_match('/href="nation=([^"]*)".*<img src="\/images\/un\.gif" hspace="6" alt="UN Member"/',
							$lines[$i],$match)) {
				$nations[$i]=$match[1];
			}
	}
	return $nations;
}

function write_to_db($nations,$region,$offset) {
	global $prefix;
	echo "&nbsp;&nbsp;&nbsp;&nbsp;Writing UN nations to database...";
	flush();
	foreach ($nations as $i=>$nation) {
		$sql[$i]="('$region','$nation')";
	}
	$sql = implode(",",$sql);
	$sql = "insert into ".$prefix."nations (region,nation) values $sql;";
	//echo $sql;
	mysql_query($sql);
	echo "done.<br/>\n";
}

function write_meta_db($region,$meta) {
	global $prefix;
	foreach($meta as $name=>$value) $$name=$value;
	$sql = "insert into ".$prefix."regions (region,delegate,nations,scan_started,scanned)
				values('$region','$delegate','$number',FROM_UNIXTIME('".time()."'),0);";
	//echo $sql;
	mysql_query($sql);
}
?>
<?php template_head("Gathering Data, Step 1 of 2"); ?>
<h1>Scanning Region for UN Nations</h1>
<a href=".">Back to main page</a> | <a href="gather.php">Back to administration page</a>
<p><strong>Note:</strong> Do not close this page while the script is running. If you do, the script will
abort and leave you with a partial scan.<br/><br/>
If there was a browser error that caused the script to stop, you can use the "offset" field to start at
the place the script reached before aborting.
</p>
<form action='' method='get'>
	<label>Region: </label><input type='text' name='region' value='<?=$region?>'/>
	<input type='submit' value='Scan'/><br/>
	<label>Offset (optional): </label><input type='text' name='offset' value='<?=$offset?>'/>
</form>
<hr/>
<?php
if ($region) {
	echo "Opened MySQL connection.<br/>\n";
	if ($offset) echo "Starting at offset $start<br/>\n";
	else {
		echo "Removing region $region_name from database.<br/>\n";
		$res = delete_region($region);
	}
	echo "Finding all UN nations within region $region:<br/>\n";
	index_region($region,$offset);
	echo "Finished.<br/>";
	mysql_close($link);
	echo "Closed MySQL connection.<br/>\n";
	echo "<hr/>\n";
	echo "Done with listing the nations. Now use <a href='scan_nations.php?region=$region'>this link</a> to count the endorsements.";
} else {
?>
<p>Enter the region you wish to scan.</p>
<?php
}
template_foot();
?>