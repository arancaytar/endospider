<?php
include("config.php");
include("includes/all.php");
$logged_in=logged_in();
$link=dbconnect();
	function get_regions() {
		global $prefix;
		$sql = "select a.region,a.nations,count(b.nation) as un_nations,a.delegate,a.scan_ended,
		unix_timestamp(scan_ended)-unix_timestamp(scan_started) as duration,unix_timestamp()-unix_timestamp(a.scan_started) as duration2
					from ".$prefix."regions a join ".$prefix."nations b on a.region=b.region
					group by b.region
					order by scan_ended is null desc,scan_ended desc;";
		//echo $sql;
		$res = mysql_query($sql);
		if (!mysql_num_rows($res)) return false;
		while($row=mysql_fetch_array($res)) $rows[]=$row; // short and elegant.
		return $rows;
	}
$regions=@get_regions();

function get_in_progress($region) {
	global $prefix;
	$sql="select count(*) as un_nations,sum(b.updated is not null) as un_scanned,
			a.nations as total_nations,a.scanned as scanned 
			from ".$prefix."regions a join ".$prefix."nations b on a.region=b.region
			where a.region='$region' group by b.region;";
	//echo $sql;
	$res = mysql_query($sql);
	$row=mysql_fetch_array($res);
	foreach ($row as $name=>$value) $$name=$value;
	if ($scanned<$total_nations) return "Phase 1: ".sprintf('%3.2f',$scanned*100/$total_nations)."% complete";
	if ($un_scanned<$un_nations) return "Phase 2: ".sprintf('%3.2f',$un_scanned*100/$un_nations)."% complete";
	return "Error";
}

?>
<?php template_head("Nationstates Endorsement Scanner"); ?>
<h1>EndoSpider - Overview</h1>
<p><a href="gather.php">Administration</a> (log-in required)</p>
<p><strong>Index</strong></p>
<hr/>
<h2>Regions currently in Database</h2>

<p>If your region is not on here, it needs to be scanned first. If you're authorized, do so with 
the Administration link.</p>
<table border="1">
	<tr>
		<?php if ($logged_in) { ?>
		<th></th>
		<?php } ?>
		<th>Region</th>
		<th>Delegate</th>
		<th>Nations</th>
		<th>UN Nations</th>
		<th>UN %</th>
		<th>Last Scan</th>
		<th>Scan Length</th>
	</tr>
<?php 
if ($regions) 
foreach($regions as $row) {
	foreach ($row as $name=>$value) $$name=$value;
	if (!$duration) $duration=$duration2;
	if($days=floor($duration/86400)) $time[0]=$days."d";
	if($hours=floor($duration/3600)%24) $time[1]=$hours."h";
	if($minutes=floor($duration/60)%60) $time[2]=$minutes."m";
	if($seconds=$duration%60) $time[3]=$seconds."s";
	$time=implode(", ",$time);
?>
	<tr>
		<?php if ($logged_in) { ?>
		<td align="center">
			<a title="Re-Scan this Region" href="index_region.php?region=<?=$region?>">
				<img alt="Reload" src="images/reload.png" />
			</a>
		</td>
		<?php } ?>
		<td><a href='region.php?region=<?=$region?>'><?=url_to_display($region)?></a></td>
		<td>
			<a title="EndoTart for Delegate" href="tarter.php?nation=<?=$delegate?>">
				<?=url_to_display($delegate)?>
			</a>
		</td>
		<td><?=$nations?></td>
		<td><a href='ranking.php?region=<?=$region?>'><?=$un_nations?></a></td>
		<td><?=sprintf('%3.2f',$un_nations/$nations*100)?>%</td>
		<td><?=($scan_ended)?$scan_ended:'<strong>'.get_in_progress($region).'</strong>'?></td>
		<td><?=$time?></td>
	</tr>
<?php
	$time=array();
	foreach ($row as $name=>$value) $$name='';
}
else { 
?>
	<tr><td colspan="6" align="center"><em>No data</em></td></tr>
<?php
}
?>
</table>

<?php

template_foot();
?>