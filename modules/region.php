<?php
include("config.php");
include("includes/all.php");
$link=@dbconnect();

foreach ($_GET as $name=>$value) $$name=$value;
$region_db=display_to_url($region);
$region=url_to_display($region);
$endorsees=@get_top_endorsees($region_db);
$endorsers=@get_top_endorsers($region_db);

?>
<?php template_head("Showing Region: $region"); ?>
<h1>Regional Details</h1>
<p><a href="gather.php">Gather data</a> (log-in required)</p>
<p><a href=".">Index</a> -&gt; <strong><?=($region)?$region:'Select Region'?></strong></p>
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

<p>Note: This data is not real-time, but as of the last manual update!</p>
<h3>Top 20 endorsed nations:</h3>
<p>
(<a href="ranking.php?region=<?=$region_db?>&amp;sort=received&amp;desc=desc">View in detail</a>)
</p>
<table border="1">
	<tr>
		<th>#</th>
		<th>Nation name</th>
		<th>Endorsements received</th>
	</tr>
<?php 
if ($endorsees) 
foreach($endorsees as $i=>$endorsee) {
	foreach ($endorsee as $name=>$value) $$name=$value;
?>
	<tr>
		<td><?=$i+1?></td>
		<td>
			<a title="View on Nationstates" href="http://www.nationstates.net/page=display_nation/nation=<?=$nation?>">
				<?=url_to_display($nation)?>
			</a>
		</td>
		<td>
			<a title="View all endorsers" href="received.php?nation=<?=$nation?>"><?=$endorsements?></a>
		</td>
	</tr>
<?php
	foreach ($endorsee as $name=>$value) $$name='';
}
else { 
?>
	<tr><td colspan="3" align="center"><em>No data</em></td></tr>
<?php
}
?>
</table>

<h3>Top 20 endorsing nations:</h3>
<p>
(<a href="ranking.php?region=<?=$region_db?>&amp;sort=given&amp;desc=desc">View in detail</a>)
</p>
<table border="1">
	<tr>
		<th>#</th>
		<th>Nation name</th>
		<th>Endorsements given</th>
	</tr>
<?php
if ($endorsers)
foreach($endorsers as $i=>$endorser) {
	foreach ($endorser as $name=>$value) $$name=$value;
?>
	<tr>
		<td><?=$i+1?></td>
		<td>
			<a title="View on Nationstates" href="http://www.nationstates.net/page=display_nation/nation=<?=$nation?>">
				<?=url_to_display($nation)?>
			</a>
		</td>
		<td>
			<a title="View all endorsees" href="given.php?nation=<?=$nation?>"><?=$endorsements?></a>
		</td>
	</tr>
<?php
	foreach ($endorser as $name=>$value) $$name='';
}
else { 
?>
	<tr><td colspan="3" align="center"><em>No data</em></td></tr>
<?php
}?>
</table>
<?php
} else {
?>
<p>Please enter a region name or select one from <a href=".">the region list</a>.</p>
<?php
}

template_foot();
?>