<?php
$start=explode(" ",microtime());
function no_region_error() {
	$im=@ImageCreate(160,80);
	$error="No region selected.";
	$background_color = ImageColorAllocate ($im, 200, 200, 200);
	$red        = ImageColorAllocate ($im, 255, 0, 0); 
	$green		= ImageColorAllocate ($im, 0, 255, 0); 
	ImageString($im,3,15,34,$error,$red);
    ImagePNG ($im);
	exit();	
}
ini_set("include_path",ini_get("include_path").":./..");
include("includes/all.php");
include("config.php");
foreach ($_GET as $name=>$value) $$name=$value;
$link=dbconnect();
if (!$debug) header ("Content-type: image/png"); // we're sending an image.
preg_match("/\/export\/png\/region\/([^\/]*)\.png.*$/",$_SERVER['REQUEST_URI'],$match);
if ($debug) echo $_SERVER['REQUEST_URI'];
//var_dump($match);
$region=$match[1];
if (!$region) no_region_error();
$region_db=display_to_url($region);
$region_display=url_to_display($region);

$im = @ImageCreate (640, 480)
     or die ("Couldn't create image.");  // or not? this hasn't happened so far.
$background_color = ImageColorAllocate ($im, 170, 170, 200);
$black = ImageColorAllocate ($im, 0, 0, 0); 
$blue        = ImageColorAllocate ($im, 0, 0, 255); 
$red        = ImageColorAllocate ($im, 255, 0, 0); 
	$green		= ImageColorAllocate ($im, 0, 150, 0); 
	$grey 		=ImageColorAllocate ($im, 150, 150, 150); 
	ImageString($im,5,200,34,"Showing: $region_display",$black);
$status=get_region_status($region_db);
$region=get_region($region_db);
if ($region)
foreach ($region as $name=>$value) {
	$name="region_$name";
	$$name=$value;
}
if (!$region_duration) $region_duration=time()-$region_scan_started2;
	if($days=floor($region_duration/86400)) $time[0]=$days."d";
	if($hours=floor($region_duration/3600)%24) $time[1]=$hours."h";
	if($minutes=floor($region_duration/60)%60) $time[2]=$minutes."m";
	if($seconds=$region_duration%60) $time[3]=$seconds."s";
	$time=@implode(", ",$time);
$percent=get_percent($region_db);
if ($status==-3) {
	 ImageString($im,3,100,80,"This region was not scanned. Contact the admin.",$red);
	 ImagePNG ($im);
	 exit;
} else if ($status==-2) {
	 ImageString($im,3,100,80,"A scan of this region is in progress.",$red);
	 ImageString($im,3,100,95,"It is in phase one, and $percent% finished.",$red);
	 ImageString($im,3,100,110,"It has been running for: $time",$red);
	 ImageString($im,3,100,150,"Phase 1:",$blue);
	 ImageFilledRectangle($im,160,150,500,165,$grey);
	 ImageFilledRectangle($im,162,152,162+(336*$percent/100),163,$blue);
	 ImageString($im,3,325,150,"$percent%",$red);
	 ImageString($im,3,100,180,"Phase 2:",$blue);
	 ImageFilledRectangle($im,160,180,500,195,$grey);
	 ImageString($im,3,325,180,"0%",$red);
	 ImagePNG ($im);
	 exit;
} else if ($status==-1) {
	 ImageString($im,3,100,80,"A scan of this region is in progress.",$red);
	 ImageString($im,3,100,95,"It is in phase two, and $percent% finished.",$red);
	 ImageString($im,3,100,110,"It has been running for: $time",$red);
	 ImageString($im,3,100,150,"Phase 1:",$blue);
	 ImageFilledRectangle($im,160,150,500,165,$grey);
	 ImageFilledRectangle($im,162,152,498,163,$blue);
	 ImageString($im,3,325,150,"100%",$red);
	 ImageString($im,3,100,180,"Phase 2:",$blue);
	 ImageFilledRectangle($im,160,180,500,195,$grey);
	 ImageFilledRectangle($im,162,182,162+floor(336*$percent/100),193,$blue);
	 ImageString($im,3,325,180,"$percent%",$red);
	 ImagePNG ($im);
	 exit;
}
ImageString($im,3,50,80,"This region has $region_total_nations nations and $region_un_nations UN members - ".
						sprintf('%3.2f',100*$region_un_nations/$region_total_nations)."%",$black);
ImageString($im,3,50,95,"Scanned on $region_scan_started. Scan took $time",$black);
ImageString($im,3,50,115,"[Delegate]",$green);
ImageString($im,3,130,115,"[Citizen]",$blue);
ImageString($im,3,200,115,"[Rogue, not endorsing delegate]",$red);
ImageString($im,3,50,130,"Top regional powers:",$black);
ImageString($im,3,350,130,"Top endotarters:",$black);
$endorsed=get_top_endorsees($region_db);
for ($i=0;$i<sizeof($endorsed);$i++) {
	$y=160+($i*15);
	$x=50;
	ImageFilledRectangle($im, $x, $y+5, $x+2, $y+7, $red);
	if ($endorsed[$i]['nation']==$region_delegate) $nation_color=$green;
	else if ($endorsed[$i]['endorsed_delegate']) $nation_color=$blue;
	else $nation_color=$red;
	ImageString($im,3,$x+5,$y,url_to_display($endorsed[$i]['nation']),$nation_color);
	ImageString($im,3,$x+200,$y,$endorsed[$i]['endorsements'],$nation_color);
}
$tarters=get_top_endorsers($region_db);
for ($i=0;$i<sizeof($tarters);$i++) {
	$y=160+($i*15);
	$x=350;
	if ($tarters[$i]['nation']==$region_delegate) $nation_color=$green;
	else if ($tarters[$i]['endorsed_delegate']) $nation_color=$blue;
	else $nation_color=$red;
	ImageFilledRectangle($im, $x, $y+5, $x+2, $y+7, $red);
	ImageString($im,3,$x+5,$y,url_to_display($tarters[$i]['nation']),$nation_color);
	ImageString($im,3,$x+200,$y,$tarters[$i]['endorsements'],$nation_color);
}
$end=explode(" ",microtime());
$time = $end[0]-$start[0]+$end[1]-$start[1];
	ImageString($im,3,100,465,"Image generated in $time seconds.",$black);
    ImagePNG ($im);
?>