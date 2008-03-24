<?php
/************************************************************************
 * scan_nations.php														*
 * function: gets the un endorsement from each un nation				*
 ************************************************************************
 * this page writes to the database and is restricted to admins			*
 ************************************************************************
 * last change: 2006-08-05 20:27										*
 *																		*
 * history:																*
 *  - 2.2.2 (2006-08-05) the database update required this page to be	*
 *						adapted.										*
 ************************************************************************/
?>
<?php
include("config.php");
include("includes/all.php"); 
if ($_COOKIE['endo']!=$admin) {
	error("You must be logged in to perform this action. Log in with the admin password on <a href='gather.php'>this page</a>.");
}
$link=@dbconnect();
header("Content-type: text/html");
function parse_nation($data,$region) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Parsing nation:<br/>\n";
	flush();
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Verifying that this nation is still in $region...";
	flush();
	while ($i<sizeof($data) && !preg_match('/region=([a-z0-9_\-]*)"/',$data[$i],$match)) {
		$i++;
	}
	if ($match[1]!=$region) {
		echo "it is not! Next nation.<br/>\n";
		return false;
	} else echo "it is.<br/>\n";
	flush();
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Parsing endorsements...";
	flush();
	while ($i<sizeof($data) && !preg_match('/Endorsements Received: ([0-9]*) \(/',$data[$i],$match)) {
		$i++;
	}
	$number = $match[1];
	if (!$number) return false;
	$nations = explode(', ',$data[$i]);
	foreach ($nations as $i=>$nation) {
		$match=array();
		preg_match('/"nation=([a-z0-9_\-]*)"/',$nation,$match);
		$endorsers[$i]=$match[1];
	}
	echo "done. There are ".sizeof($endorsers)." endorsements.<br/>\n";
	flush();
	return $endorsers;
}

function scan_list($list,$region) {
	if (!$list) return false;
	foreach ($list as $i=>$nation) {
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Processing nation #$i: $nation<br />\n";
		$data = get_nation($nation);
		$endos = parse_nation($data,$region);
		write_to_db($nation,$endos);
		echo "&nbsp;&nbsp;&nbsp;&nbsp;Done with this nation.<br/>\n";
	}
	return true;
}

function get_nation($nation_name) {
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Downloading page for $nation_name from NationStates...";
	flush();
	$url= "http://www.nationstates.net/page=display_nation/nation=$nation_name";
	$data_parse = curl_get($url);
	for ($i=0;$i<sizeof($data_parse);$i++) {
		if (preg_match('/src="\/images\/smalleyelogo\.jpg" width="120" height="90"/',
					$data_parse[$i])) $data=$data_parse;
	}
		while (!$data) {
			echo "<br/>\n<strong>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;ERROR! Redialing...</strong>"; 
			$data_parse = curl_get($url);
			for ($i=0;$i<sizeof($data_parse);$i++) {
				if (preg_match('/src="\/images\/smalleyelogo\.jpg" width="120" height="90"/',
								$data_parse[$i])) $data=$data_parse;
			}
		}
	echo "done.<br/>\n";
	flush();
	return $data;
}

function write_to_db($nation_name,$endos) {
	global $prefix;
	echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;Saving endorsements for $nation_name to database...";
	if (!$endos) {
		$sql = "update ".$prefix."nations set endorsements=0, updated=from_unixtime(unix_timestamp()) where nation='$nation_name';";
		mysql_query($sql);
		return false;
	}
	foreach ($endos as $i=>$endo) $values[$i]="('$endo','$nation_name')\n";
	$values=implode(",",$values);
	$sql ="insert into ".$prefix."endorsements (endorser,endorsee) values
		$values;";
	mysql_query($sql);
	$ends = sizeof($endos);
	$sql = "update ".$prefix."nations set endorsements='$ends', updated=from_unixtime(unix_timestamp()) where nation='$nation_name';";
	mysql_query($sql);
	echo "done.<br/>\n";
}
	
function get_within_region($region) {
	global $prefix;
	$sql = "select nation from ".$prefix."nations where region='$region' order by nation;";
	$res = mysql_query($sql);
	for ($i=0;$row=mysql_fetch_array($res);$i++) {
		$rows[$i]=$row[0];
	}
	return $rows;
}

foreach ($_GET as $name=>$value) $$name=$value;
$region=display_to_url($region);
$region_show=url_to_display($region);
?>
<?php template_head("Gathering data, step 2 of 2"); ?>
<h1>Scanning UN Nations for Endorsements</h1>
<p><a href=".">Back to main page</a> | <a href="gather.php">Back to administration page</a><br/><br/></p>
<form action='' method='get'>
	<label>Region: </label><input type='text' name='region' value='<?=$region?>'/>
	<input type='submit' value='Scan'/><br/>
	<label>Start with nation (optional): </label><input type='text' name='offset' value='<?=$offset?>'/>
</form>
<p><strong>Note:</strong> Do not close this page while the script is running. If you do, the script will
abort and leave you with a partial scan.<br/><br/>
If there was a browser error that caused the script to stop, you can use the "offset" field to start at
the place the script reached before aborting. Enter the last nation name scanned successfully. The script
goes through nations in alphabetical order.
</p>
<hr/>
<?php
	if ($region) {
		echo "Scanning endorsement network in region $region.<br/>\n";
		echo "Getting list of UN nations in region...";
		flush();
		$list = get_within_region($region);
		echo "done. There are ".sizeof($list)." UN nations here.<br/>\n";
		flush();
		echo "Preparing to scan all nations:<br/>\n";
		if ($offset) {
			echo "Starting at nation $offset.<br/>\n";
			$i=0;
			while ($i<sizeof($list) && $list[$i]!=$offset) {
				echo "Bypassing nation ".$list[$i]."<br/>\n";
				$i++;
			}
			for ($j=$i;$j<sizeof($list);$j++) $filtered[$j]=$list[$j];
			$list=$filtered; 
		}
		$success = scan_list($list,$region);
		if ($success) {
			mysql_query("update ".$prefix."regions set scan_ended=from_unixtime('".time()."') 
							where region='$region';");
			mysql_close($link);
?>
Finished. You can now look at the stats <a href='.?region=<?=$region?>'>here</a>.
<?php
		} else {
?>
<strong>ERROR!</strong> No UN nations found. This may be because:
<ol>
	<li>there are none or</li>
	<li>the region name is misspelled</li>
	<li>you need to run <a href='index_region.php?region=<?=$region?>'>this scanner</a> first.</li>
	<li>the offset <?=$offset?> was not in the nation list.</li>
</ol>
<?php
		}
	} else {
?>
Please enter a region name.
<?php
	}
template_foot();
?>