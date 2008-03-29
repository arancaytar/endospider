<?='<?xml version="1.0" encoding="iso-8859-1"?>'?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>EndoSpider | <?=$title?></title>
 
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<!-- related documents -->
<base href="http://<?=$_SERVER['HTTP_HOST'] . l()?>" />
<link rel="start" href="<?=$start?>" />
<?php if ($prev) { ?><link rel="prev"  href="<?=$prev?>" /> <?php } ?>
<?php if ($next) { ?><link rel="next"  href="<?=$next?>" /> <?php } ?>
<link rel="contents" href="." />
<link rel="help" href="help" />

<!-- stylesheets -->
<link rel="stylesheet" 
      type="text/css" 
      media="all" 
      href="style/default.css" />
<link rel="stylesheet" 
      type="text/css" 
      media="screen" 
      href="style/screen.css" />
<link rel="stylesheet" 
      type="text/css" 
      media="print" 
      href="style/print.css" />
<script type="text/javascript" src="style/scripts/jquery.js"></script>
<script type="text/javascript" src="style/scripts/jquery.form.js"></script>
</head>

<body>
	<div id="content">
		
	<div class="filled-box" id="title"> 
    	<h1>EndoSpider</h1>

 	</div>
	<?php
	if (!empty($page)) {
  ?>
  <div class="filled-box" id="navside">
    <h2 class="navside">Navigation</h2> 
    <?=$menu?>
  </div>	
	<?php
	}
	?>
	<div id="main">
		<div id="main2">
			<h2><?=$title?></h2>
      <div id="messages">
      <?php 
      if (count($messages)) {
        print '<ul><li class="message">'. implode('</li><li class="message">', $messages) .'</li></ul>';
      }
      ?>
      </div>
			<?=$content?>			
		</div>
	</div>
</div>
<div class="filled-box" id="footer">
<em>EndoSpider <?=VERSION?> rv:<?=VERSION_RV?></em><br/>
<a rel="certificate" href="http://validator.w3.org/check?uri=referer"><img
					src="http://www.w3.org/Icons/valid-xhtml10"
					alt="Valid XHTML 1.0 Strict" height="31" width="88" style="float:left" /></a>
 <a rel="certificate" href="http://jigsaw.w3.org/css-validator">
  <img style="border:0;width:88px;height:31px;float:left"
       src="http://jigsaw.w3.org/css-validator/images/vcss"
       alt="Valid CSS!" />
 </a>
  This page can be viewed in any standards-compliant browser.<br/>
  Recommended: <a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=96065&amp;t=54">Firefox 
  3.0</a> or <a href="http://www.opera.com">Opera 9.5</a>.<hr/>
<small><em>EndoSpider was developed by Arancaytar, aka <a href="mailto:ermarian@gmail.com">Ermarian</a>.
 Visit my site at <a href="http://ermarian.net">The Ermarian Network</a>.</em></small></div>
</body>
</html>