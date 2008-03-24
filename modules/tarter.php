<?php
/************************************************************************
 * tarter.php															*
 * function: displays the endorsements a nation has not given.			*
 ************************************************************************
 * this page is public; nothing is written to the database.				*
 ************************************************************************
 * last change: 2006-08-05 19:50										*
 *																		*
 * history:																*
 *  - 2.2.2 (2006-08-05) xhtml was fixed. minor display formatting, 	*
 *					including the decimal trimming of the percentage.	*
 ************************************************************************/
?>
<?php
include("config.php");
include("includes/all.php");
$link=@dbconnect();
global $st,$limit;
$st=0;
$limit = 25;
foreach ($_GET as $name=>$value) $$name=$value;
$nation_db=display_to_url($nation);
$nation=url_to_display($nation);
global $endorsed;
global $total;
if ($nation) {
	$region_db=get_region_name($nation_db);
	if ($region_db) {
		$region=url_to_display($region_db);
		$rows=get_not_given($region_db,$nation_db);
	}
}

function get_not_given($region,$nation) { // this is where grammar ends.
	global $st,$limit,$prefix,$endorsed,$total;
	$sql = "select count(*) from ".$prefix."endorsements where endorser='$nation';";
	$count = mysql_query($sql);
	$count= mysql_fetch_array($count);
	$endorsed=$count[0];
	$sql = "select count(*) from ".$prefix."nations where region='$region' and nation!='$nation';";
	$total = mysql_query($sql);
	$total= mysql_fetch_array($total);
	$total=$total[0];
	$sql = "create temporary table ".$prefix."temp_tartage
			select nation,0 as endorsed
			from ".$prefix."nations	where region='$region' and nation!='$nation';";
	mysql_query($sql);
	//echo mysql_error();
	$sql = "update ".$prefix."temp_tartage a join ".$prefix."endorsements b
				on a.nation=b.endorsee
				set endorsed=1 where b.endorser='$nation';";
	mysql_query($sql);
	$sql = "select nation from ".$prefix."temp_tartage where endorsed=0	order by nation limit $st,$limit;";
	$res = mysql_query($sql);
	for ($i=0;$row=mysql_fetch_array($res);$i++) {
		$rows[$i]=$row[0];
	}
	return $rows;
}

function get_region_name() {
	global $prefix;
	foreach ($_GET as $name=>$value) $$name=$value;
	$nation_db=display_to_url($nation);
	$sql = "select region from ".$prefix."nations where nation='$nation_db';";
	$res = mysql_query($sql);
	$res = mysql_fetch_array($res);
	return $res[0];
}
?>
<?php template_head("Tart list for: $nation");?>
<h1>Nation Details</h1>
<p><a href=".">Back to main page</a> | <a href="gather.php">Admin panel</a></p>
<p><a href=".">Index</a> -&gt; <a href="region.php?region=<?=$region_db?>"><?=$region?></a>
 -&gt; <strong><?=$nation?> -&gt; Tarting</strong></p>
<form action='' method='get'>
<p>
	<label>Nation: </label><input type='text' name='nation' value='<?=$nation?>'/>
	<input type='submit' value='display'/></p>
</form>
<hr/>
<?php
if ($nation) { 
	if ($region) {
?>
<h2>Helping <em><?=$nation?></em> tart</h2>
<p><a href="received.php?nation=<?=$nation_db?>">All endorsements received by <?=$nation?></a> | 
<a href="received.php?nation=<?=$nation_db?>&amp;returned=yes">Returned</a> | 
<a href="received.php?nation=<?=$nation_db?>&amp;returned=no">Unreturned</a><br/>
<a href="given.php?nation=<?=$nation_db?>">All endorsements given by <?=$nation?></a> | 
<a href="given.php?nation=<?=$nation_db?>&amp;returned=yes">Returned</a> | 
<a href="given.php?nation=<?=$nation_db?>&amp;returned=no">Unreturned</a><br/>
<strong>Nations <em>not</em> endorsed by <?=$nation?></strong></p>
<hr/>
<p>
Of <strong><?=$total?></strong> nations in <strong><?=$region?></strong>, <strong><?=$nation?></strong> has endorsed <strong><?=$endorsed+0?></strong> or
	<strong><?=sprintf('%3.2f',100*$endorsed/$total)?>%</strong>. <br/>
	<strong><?=$total-$endorsed?></strong>, or 
	<strong><?=sprintf('%3.2f',100*($total-$endorsed)/$total)?>%</strong> remain unendorsed.
</p>
<p>
<?php pages($total-$endorsed,"nation=$nation_db"); ?>
</p>
<?php 
		if ($rows) {
?>
<ul>
<?php
			foreach ($rows as $row) {
?>
	<li>
		<a href="http://www.nationstates.net/page=display_nation/nation=<?=$row?>"><?=url_to_display($row)?></a>
	</li>
<?php
			}
?>
</ul>
<?php
		} else { 
?>
	<p><em>There are no nations this nation has not endorsed.</em></p>		
<?php
		}
	} else {
?>

<p>The nation you entered could not be found in the database. This may because the region it is in
was never scanned, was purged from the database, or was not scanned since the nation entered that region.
In any case, the region needs to be scanned. If you are authorized, you can <a href="gather.php">do so</a>.</p>
<?php
	}
} else { 
?>
<p>Please enter a nation name (lower-case, spaces replaced with underscores).</p>
<?php
}
mysql_close($link);
template_foot();
?>