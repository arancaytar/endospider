<?php
/************************************************************************
 * given.php															*
 * function: displays the endorsements a nation has given.				*
 * these can be filtered, showing all, returned or unreturned.			*
 ************************************************************************
 * this page is public; nothing is written to the database.				*
 ************************************************************************
 * last change: 2006-08-05 19:34										*
 *																		*
 * history:																*
 *  - 2.2.2 (2006-08-05) xhtml was fixed. minor display formatting, 	*
 *					including the decimal trimming of the percentage	*
 ************************************************************************/
?>
<?php
include("config.php");
include("includes/all.php");
foreach ($_GET as $name=>$value) $$name=$value;
$nation_db=display_to_url($nation);
$nation=url_to_display($nation);
$link=dbconnect();
global $count;
if ($nation) {
	$region_db=get_region_name($nation);
	if ($region_db) {
		$region=url_to_display($region_db);
		$rows=get_given();
	}
}

function get_given() { // yo, i got given that endorsement!
	$st=0;
	$limit = 25;
	global $prefix;
	global $count;
	global $total;
	foreach ($_GET as $name=>$value) $$name=$value;
	$sql = "create temporary table ".$prefix."temp_given
				select endorsee as nation,0 as is_returned from ".$prefix."endorsements where endorser='$nation';";
	mysql_query($sql);
	$sql = "update ".$prefix."temp_given a join ".$prefix."endorsements b on a.nation=b.endorser 
				set a.is_returned=1 where b.endorsee='$nation';";
	mysql_query($sql);
	if ($returned=='yes') $where='where is_returned=1';
	else if ($returned=='no') $where='where is_returned=0';
	if (!$where) $unwhere='where is_returned=1';
	$sql = "select count(*) from ".$prefix."temp_given $where;";
	$count = mysql_query($sql);
	$count= mysql_fetch_array($count);
	$count=$count[0];
	$sql = "select count(*) from ".$prefix."temp_given $unwhere;";
	$total = mysql_query($sql);
	$total= mysql_fetch_array($total);
	$total=$total[0];
	$sql = "select * from ".$prefix."temp_given $where order by nation limit $st,$limit;";
	$res = mysql_query($sql);
	//echo mysql_error();
	for ($i=0;$row=mysql_fetch_array($res);$i++) {
		$rows[$i]=$row;
	}
	return $rows;
}

function get_region_name() {
	global $prefix;
	foreach ($_GET as $name=>$value) $$name=$value;
	$sql = "select region from ".$prefix."nations where nation='$nation';";
	$res = mysql_query($sql);
	$res = mysql_fetch_array($res);
	return $res[0];
}
?>
<?php template_head("Details for: $nation"); ?>
<h1>Nation Details</h1>
<p><a href=".">Back to main page</a> | <a href="gather.php">Admin panel</a></p>
<p><a href=".">Index</a> -&gt; <a href="region.php?region=<?=$region_db?>"><?=$region?></a>
 -&gt; <strong><?=$nation?> -&gt; Endorsements given</strong></p>
<form action='' method='get'>
<p>
	<label>Nation: </label><input type='text' name='nation' value='<?=$nation?>'/>
	<input type='submit' value='display'/></p>
</form>
<hr/>
<?php
$color=array(0=>'red',1=>'green');
if (!$returned) {
	$nreturned=$total;
	$total=$count;
} elseif ($returned=='yes') $nreturned=$count;
else $nreturned=$total-$count;
if ($nation) { 
	if ($region) {
?>
<h2>Endorsements given by: <?=$nation?> in <?=$region?></h2>
<p><a href="received.php?nation=<?=$nation_db?>">All endorsements received by <?=$nation?></a> | 
<a href="received.php?nation=<?=$nation_db?>&amp;returned=yes">Returned</a> | 
<a href="received.php?nation=<?=$nation_db?>&amp;returned=no">Unreturned</a><br/>
<?=($returned)?"<a href='?nation=$nation_db'>":"<strong>"?>All endorsements given by <?=$nation?><?=($returned)?'</a>':'</strong>'?> | 
<?=($returned!='yes')?"<a href='?nation=$nation_db&amp;returned=yes'>":"<strong>"?>Returned<?=($returned!='yes')?'</a>':'</strong>'?> |
<?=($returned!='no')?"<a href='?nation=$nation_db&amp;returned=no'>":"<strong>"?>Unreturned<?=($returned!='no')?'</a>':'</strong>'?><br/>
<a href="tarter.php?nation=<?=$nation_db?>">Nations <em>not</em> endorsed by <?=$nation?></a></p>
<hr/>
<p>Of <strong><?=$total?></strong> endorsements given out by <strong><?=$nation?></strong>, <strong><?=$nreturned?></strong> were returned - or
	<strong><?=($total)?sprintf('%3.2f',100*$nreturned/$total):'0'?>%</strong>.</p>
<p><?php pages($count,"nation=$nation_db&amp;returned=$returned"); ?></p>
<table border="1">
	<tr>
		<th>Nation</th>
		<th>Returned</th>
	</tr>
<?php 
		if ($rows) {
			foreach ($rows as $row) {
				foreach ($row as $name=>$value) $$name=$value;
?>
	<tr>
		<td><a href="http://www.nationstates.net/page=display_nation/nation=<?=$nation?>"><?=url_to_display($nation)?></a></td>
		<td style='background:<?=$color[$is_returned]?>'><a href="received.php?nation=<?=$nation?>" title="Other endorsers"><?=($is_returned)?'Yes':'No'?></a></td>
	</tr>
<?php
			}
		} else {
?>
	<tr><td colspan="2" align="center"><em>No data</em></td></tr>
<?php
		}
?>
</table>
<?php
	} else {
?>
<p>The nation you entered could not be found in the database. This may because the region it is in
was never scanned, was purged from the database, or was not scanned since the nation entered that region.
In any case, the region needs to be scanned. If you are authorized, you can <a href="gather.php">do so</a>.</p>
<?php
	}
} else { 
?>
<p>Please enter a nation name.</p>
<?php
}
mysql_close($link);

template_foot();
?>