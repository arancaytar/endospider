<?php
include("config.php");
include("includes/all.php");
$link=dbconnect();

	function get_ranking($region_db,$sort,$desc,$st) {
		global $prefix;
		$st=0;
		$limit=25;
		foreach($_GET as $name=>$value) $$name=$value;
		global $count;
		$sql = "select count(*) from ".$prefix."nations where region='$region';";
		$count=mysql_query($sql);
		//echo mysql_error();
		$count=@mysql_fetch_array($count);
		$count=$count[0];
		if (!$sort) $sort='received';
		if (!$desc) $desc='desc';
		$sql = "create temporary table ".$prefix."ranking select nation,0 as given,0 as received from ".$prefix."nations where region='$region_db';";
		mysql_query($sql);
		//echo $sql;
		$sql = "create temporary table ".$prefix."received select endorsee as nation,count(*) as received from ".$prefix."ranking a join ".$prefix."endorsements b on a.nation=b.endorsee group by b.endorsee;";
		//echo $sql;
		mysql_query($sql);
		$sql = "create temporary table ".$prefix."given select endorser as nation,count(*) as given from ".$prefix."ranking a join ".$prefix."endorsements b on a.nation=b.endorser group by b.endorser;";
		//echo $sql;
		mysql_query($sql);
		$sql = "update (".$prefix."ranking a left outer join ".$prefix."received b on a.nation=b.nation) left outer join ".$prefix."given c on a.nation=c.nation set a.received=b.received,a.given=c.given;";
		//echo $sql;
		mysql_query($sql);
		$sql = "select nation,given,received from ".$prefix."ranking
					order by $sort $desc limit $st,$limit;";
		$res = mysql_query($sql);
		//echo $sql;
		for ($i=0;$row=mysql_fetch_array($res);$i++) {
			$rows[$i]=$row;
		}
		return $rows;
	}
global $count;
$sort='received';
$desc='desc';
foreach($_GET as $name=>$value) $$name=$value;
$region_db=display_to_url($region);
$region=url_to_display($region);
$nations=get_ranking($region_db,$sort,$desc,$st);

?>
<?php template_head("Ranking for: $region"); ?>
<h1>Endorsement Ranking</h1>
<p><a href=".">Back to main page</a> | <a href="gather.php">Admin panel</a></p>
<p><a href=".">Index</a> -&gt; <a href="region.php?region=<?=$region_db?>"><?=$region?></a>
 -&gt; <strong>Ranking</strong></p>
<form action='' method='get'>
<p>
	<label>Region: </label><input type='text' name='region' value='<?=$region?>'/>
	<input type='submit' value='display'/></p>
</form>
<hr/>
<?php
if ($region) { 
?>
<h2><?=$region?></h2>
<p><?php pages($count,"region=$region_db&amp;sort=$sort&amp;desc=$desc"); ?></p>
<table border="1">
	<tr>
		<th>#</th>
		<th>
			<a href="?region=<?=$region_db?>&amp;sort=nation&amp;desc=<?=($sort=='nation'&&$desc=='asc')?'desc':'asc'?>">Nation name</a>
			<?php if ($sort=='nation') {?><img src='images/<?=$desc?>.png' alt='<?=$desc?>ending' /><?php } ?>
		</th>
		<th>
			<a href="?region=<?=$region_db?>&amp;sort=received&amp;desc=<?=($sort=='received'&&$desc=='desc')?'asc':'desc'?>">Endorsements received</a>
                        <?php if ($sort=='received') {?><img src='images/<?=$desc?>.png' alt='<?=$desc?>ending' /><?php } ?>
		</th>
                <th>
			<a href="?region=<?=$region_db?>&amp;sort=given&amp;desc=<?=($sort=='given'&&$desc=='desc')?'asc':'desc'?>">Endorsements given</a>
                        <?php if ($sort=='given') {?><img src='images/<?=$desc?>.png' alt='<?=$desc?>ending' /><?php } ?>
		</th>
	</tr>
<?php 
if ($nations) 
foreach($nations as $i=>$item) {
	foreach ($item as $name=>$value) $$name=$value;
?>
	<tr>
		<td><?=$st+$i+1?></td>
		<td>
			<a title="View on Nationstates" href="http://www.nationstates.net/page=display_nation/nation=<?=$nation?>">
				<?=url_to_display($nation)?>
			</a>
		</td>
		<td>
			<a title="View all endorsers" href="received.php?nation=<?=$nation?>"><?=$received?></a>
		</td>
                <td> 
                        <a title="View all endorsees" href="given.php?nation=<?=$nation?>"><?=$given?></a>
                </td>
	</tr>
<?php
	foreach ($item as $name=>$value) $$name='';
}
else { 
?>
	<tr><td colspan="4" align="center"><em>No data</em></td></tr>
<?php
}
?>
</table>
<?php
} else {
?>
<p>Please enter a region name (lower-case, spaces replaced with underscores).</p>
<?php
}
mysql_close($link);
template_foot();
?>