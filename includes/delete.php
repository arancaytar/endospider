<?php
function delete_region($region) {
	global $prefix;
	$sql = "UPDATE ".$prefix."nations b LEFT OUTER JOIN ".$prefix."endorsements a ON a.endorser=b.nation
				SET a.flag=1
				WHERE b.region='$region';";
	//echo $sql;
	mysql_query($sql);
	$sql = "DELETE FROM ".$prefix."endorsements WHERE flag=1;";
	//echo $sql;
	$res1 = mysql_query($sql);
	$sql = "DELETE FROM ".$prefix."nations WHERE region='$region';";
	//echo $sql;
	$res2 = mysql_query($sql);
	$sql = "DELETE FROM ".$prefix."regions WHERE region='$region';";
	//echo $sql;
	$res3 = mysql_query($sql);
	return $res1 && $res2 && $res3;
}
?>