<?php 
include("config.php");
include("includes/all.php");
foreach($_POST as $name=>$value) $$name=display_to_url($value);
$link=@dbconnect();
if ($delete=='all') {
	mysql_query('TRUNCATE '.$prefix.'nations;');
	mysql_query('TRUNCATE '.$prefix.'endorsements;');
	mysql_query('TRUNCATE '.$prefix.'regions;');
	$msg = "All data was deleted.";
} else if ($delete_region) {
	$res = delete_region($delete_region);
	$msg = "All data pertaining to region $delete_region was deleted.";
}
mysql_close($link);
?>
<<??>?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>EndoSpider - Nationstates Endorsement Scanner</title>
 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- related documents -->
<link rel="start" href="index.php" />
<link rel="prev"  href="" />
<link rel="next"  href="" />
<link rel="contents" href="sitemap.html" />
<link rel="help" href="about.html" />
<link rel="stylesheet" 
      type="text/css" 
      media="screen" 
      href="style/default.css" />

</head>

<body>
<h1>Clearing Database</h1>
<p><a href=".">Back to main page</a> | <a href="gather.php">Back to admin panel</a> (log-in required)</p>
<?=$msg?>
<h2>Clear all data</h2>
<form action='' method='post' onSubmit='return confirm("Warning: This will clear all data gathered by EndoSpider. Click OK to continue.")'>
	<input type="hidden" name="delete" value="all"/>
	<input type="submit" value="Delete" />
</form>
<h2>Delete specific region</h2>
<form action='' method='post' onSubmit='return confirm("Warning: This will clear all data relevant to "+this.delete_region.value+". Click OK to continue.")'>
	<input type="text" name="delete_region" />
	<input type="submit" value="Delete" />
</form>
<div class="filled-box" id="footer"><a href="http://validator.w3.org/check?uri=referer"><img
					src="http://www.w3.org/Icons/valid-xhtml10"
					alt="Valid XHTML 1.0 Strict" height="31" width="88" style="float:left" /></a>
 <a href="http://jigsaw.w3.org/css-validator">
  <img style="border:0;width:88px;height:31px;float:left"
       src="http://jigsaw.w3.org/css-validator/images/vcss"
       alt="Valid CSS!" />
 </a>
  This page can be viewed in any standards-compliant browser.<br/>
  Recommended: <a href="http://www.spreadfirefox.com/?q=affiliates&id=96065&t=54">Firefox 
  1.5</a> or <a href="http://www.opera.com">Opera 9</a>.<hr/>
<small><em>EndoSpider was developed by Arancaytar, aka <a href="mailto:ermarian@gmail.com">Ermarian</a>.
 Visit my site at <a href="http://ermarian.net">The Ermarian Network</a>.</em></small></div>
</body>
</html>

