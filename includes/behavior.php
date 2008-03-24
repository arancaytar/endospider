<?php

function generate_behavior_table($region) {
	global $prefix;
	$region=display_to_url($region);
	if (mysql_query("desc ".$prefix."temp_".$region."_behavior;")) return true; // table exists
	echo "starting...";
	flush();
	$sql = "create temporary table ".$prefix."temp_".$region."_behavior
				select nation,0 as received,0 as given,0 as returned,0 as delegate_endorsed
				from ".$prefix."nations where region='$region';";
	mysql_query($sql);
	echo $sql;
	flush();
	mysql_query("create temporary table ".$prefix."temp_".$region."_given
				select b.endorser as nation,count(*) as given from
				".$prefix."temp_".$region."_behavior a join ".$prefix."endorsements b
				on a.nation=b.endorser group by b.endorser;");
	echo "given";
	flush();
	$sql="create temporary table ".$prefix."temp_".$region."_received
				select b.endorsee as nation,count(*) as received from
				".$prefix."temp_".$region."_behavior a join ".$prefix."endorsements b
				on a.nation=b.endorsee group by b.endorsee;";
	echo $sql;
	mysql_query($sql);
	echo "received";
	flush();
	mysql_query("create temporary table ".$prefix."temp_".$region."_returned
					select b.endorser as nation,count(*) as returned from 
					".$prefix."temp_".$region."_behavior a join
					(endospider_endorsements b join endospider_endorsements c 
					on b.endorser=c.endorsee and b.endorsee=c.endorser) 
					on a.nation=b.endorser
					group by b.endorser;");
	mysql_query("update ".$prefix."temp_".$region."_behavior a join
					".$prefix."temp_".$region."_received b on a.nation=b.nation
					join ".$prefix."temp_".$region."_given c on a.nation=c.nation
					join ".$prefix."temp_".$region."_returned d on a.nation=d.nation
					set a.received=b.received, a.given=c.given, a.returned=d.returned;");
	echo mysql_error();
	return true;
}

function get_status($nation,$region) {
	$region=display_to_url($region);
	global $prefix;
	if (!generate_behavior_table($region)) return false;
	$sql = "select count(*) from ".$prefix."temp_".$region."_behavior;";
	echo $sql;
	$total=mysql_fetch_array(mysql_query($sql));
	$total=$total[0];
	$sql = "select * from ".$prefix."temp_".$region."_behavior where nation='$nation';";
	$res=mysql_query($sql);
	$row=mysql_fetch_array($res);
	$tarting= ($row['given']*2>=$total); // has endorsed more than half of all nations?	
	$ingrate= ($row['returned']*2<=$row['received']); // has returned less than half of all endorsements?
	$apathetic = ($row['given']==0); // is not endorsing anyone?
	$newbie = ($apathetic&&$row['received']==0); // has not been endorsed by anyone
	$power = ($row['received']*4>=$total); // has received a darn lot of endorsements
	$success = ($row['returned']*4>=$row['given']); // gets a lot of swappage?
	$side= ($row['endorsed_delegate'])?'loyal':'rogue';
	if ($newbie) return 'newbie'; // newbies can't really do much.
	if ($apathetic) return 'apathetic'; // neither can apathetic people.
	/* did he endorse a lot of people? if so, did he get a lot? and is he endorsing the delegate? */
	if ($tarting && $power) return "$side regional power";
	if ($tarting) return ($success)?"successful $side tarter":"unsucessful $side tarter";
	if ($power && $side=='rogue') return 'invader candidate';
	if ($ingrate) return "$side leech";
	return "$side swapper";
}

?>