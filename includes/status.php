<?php

function get_region_status($region) {
	global $prefix;
	$sql="select scan_ended,count(*) as un_nations,sum(b.updated is not null) as un_scanned,
			a.nations as total_nations,a.scanned as scanned 
			from ".$prefix."regions a join ".$prefix."nations b on a.region=b.region
			where a.region='$region' group by b.region;";
	//echo $sql;
	$res = mysql_query($sql);
	$row=mysql_fetch_array($res);
	if (!$row) return -3;
	foreach ($row as $name=>$value) $$name=$value;
	if ($scan_ended) return 1;
	if ($scanned<$total_nations) return -2;
	if ($un_scanned<$un_nations) return -1;
	if ($un_scanned==$un_nations) return 1;
	return 0;
}		
function get_percent($region) {
	global $prefix;
	$sql="select count(*) as un_nations,sum(b.updated is not null) as un_scanned,
			a.nations as total_nations,a.scanned as scanned 
			from ".$prefix."regions a join ".$prefix."nations b on a.region=b.region
			where a.region='$region' group by b.region;";
	//echo $sql;
	$res = mysql_query($sql);
	$row=mysql_fetch_array($res);
	if (!$row) return -3;
	foreach ($row as $name=>$value) $$name=$value;
	if ($scanned<$total_nations) return sprintf('%3.2f',$scanned/$total_nations*100);
	if ($un_scanned<$un_nations) return sprintf('%3.2f',$un_scanned/$un_nations*100);
	return 100;
}

function get_region($region) {
	global $prefix;
	$sql = "select a.region,a.delegate,a.nations as total_nations,count(*) as un_nations,
			unix_timestamp(scan_started) as scan_started2,scan_started,unix_timestamp(scan_ended)-unix_timestamp(scan_started) as duration
			from ".$prefix."regions a join ".$prefix."nations b on a.region=b.region
			where a.region='$region'
			group by b.region;";
	$res=mysql_query($sql);
//	echo mysql_error();
	return mysql_fetch_array($res);
}
	function get_top_endorsees($region) {
		global $prefix,$debug;
		$region_meta=get_region($region);
		$delegate=$region_meta['delegate'];
		$sql = "create temporary table ".$prefix."temp_endorsees select a.endorsee as nation,
						count(*) as endorsements, 0 as endorsed_delegate
				 from (".$prefix."endorsements a join ".$prefix."nations b on a.endorsee=b.nation and region='$region')
				 where region='$region'	 group by a.endorsee";
		mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		$sql = "update ".$prefix."temp_endorsees a join ".$prefix."endorsements b
					on a.nation=b.endorser and b.endorsee='$delegate' set endorsed_delegate=1";
		mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		$sql = "select * from ".$prefix."temp_endorsees order by endorsements desc limit 0,20;";
		$res = mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		while ($row=mysql_fetch_array($res)) $rows[]=$row;
		return $rows;
	}
	function get_top_endorsers($region) {
		global $debug,$prefix;
		$region_meta=get_region($region);
		$delegate=$region_meta['delegate'];
		$sql = "create temporary table ".$prefix."temp_endorsers select a.endorser as nation,
						count(*) as endorsements, 0 as endorsed_delegate
						 from (".$prefix."endorsements a join ".$prefix."nations b on a.endorser=b.nation and region='$region')
				 group by a.endorser
					order by endorsements desc limit 0,20;";
		mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		$sql = "update ".$prefix."temp_endorsers a join ".$prefix."endorsements b
					on a.nation=b.endorser and b.endorsee='$delegate' set endorsed_delegate=1";
		mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		$sql = "select * from ".$prefix."temp_endorsers order by endorsements desc limit 0,20;";
		$res = mysql_query($sql);
		if ($debug) echo $sql.mysql_error();
		for ($i=0;$row=mysql_fetch_array($res);$i++) {
			$rows[$i]=$row;
		}
		return $rows;
	}
?>