<?php

define('AUTH_ADMIN', 'auth_admin');

function logged_in() {
	include("config.php");
	if ($_COOKIE['endo']==$admin) return true;
	else return false;
}

function auth() {
	if ($_COOKIE['endospider_admin'] == AUTH_ADMIN_PASS) return true;
	if (!empty($_COOKIE['endospider_admin'])) {
	  message(t('The password you entered is wrong.'));
	}
  return false; 
}

function log_in() {
  include("config.php");
  if ($_POST['endo']==$admin) {
    setcookie('endo',$admin);
    $_COOKIE['endo']=$admin;
    return true;
  }
  else {
    if ($_POST['endo']) {
      global $msg;
      $msg='This is the wrong password';
    }
    return false;
  }
}

function log_out() {
  setcookie('endo','',-1);
  $_COOKIE['endo']='';
  global $msg;
  $msg='You are now logged out.';
}
if ($_POST['log']=='out') log_out();
if (logged_in() || log_in()) {
  $logged_in=true;
} else {
  $logged_in = false;

}
global $msg;
?>
<<??>?xml version="1.0" encoding="iso-8859-1"?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
<head>
<title>EndoSpider - Log In</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />

<!-- related documents -->
<link rel="stylesheet" 
      type="text/css" 
      media="screen" 
      href="style/default.css" />
</head>

<body>
<h1>Gather Data</h1>
<p><a href=".">Back to the main page</a></p>
<?php
if(!$logged_in) {
?>
<h2>Log in</h2>
<p><span style="color:red;font-weight:bold"><?=$msg?></span></p>
<form action='' method='post'>
<p>
  <input type='password' name='endo' />
  <input type='submit' value='Log in' /></p>
</form>
<?php
} else { 
?>
<h2>You are logged in</h2>
<p>If you wish, you can start gathering data now. Enter the name of the region you wish to scan. The
program will start right away with listing UN nations, which should take several minutes for large regions.
The scan itself will begin only after all UN nations have been found, and depending on regional size it could
take up to an hour.</p>
<form action='index_region.php' method='get'>
  <p><input type='text' name='region' />
  <input type='submit' value='Begin' /></p>
</form>
<p>Note that the database <strong>must</strong> be cleared of this region before you can scan anew. This
is done automatically when the scan begins. If you wish to save a dump of the table, this is the time to do it.<br/>
<br/>
You can also <a href="reset.php">clear the database</a> manually (completely or by region).</p>
<p>When you are finished, you can log out:</p>
<form action='' method='post'>
  <p><input type='hidden' name='log' value='out' />
  <input type='submit' value='Log out' /></p>
</form>
<?php
}
?>
<div class="filled-box" id="footer"><a href="http://validator.w3.org/check?uri=referer"><img
          src="http://www.w3.org/Icons/valid-xhtml10"
          alt="Valid XHTML 1.0 Strict" height="31" width="88" style="float:left" /></a>
 <a href="http://jigsaw.w3.org/css-validator">
  <img style="border:0;width:88px;height:31px;float:left"
       src="http://jigsaw.w3.org/css-validator/images/vcss"
       alt="Valid CSS!" />
 </a>
  This page can be viewed in any standards-compliant browser.<br/>
  Recommended: <a href="http://www.spreadfirefox.com/?q=affiliates&amp;id=96065&amp;t=54">Firefox 
  1.5</a> or <a href="http://www.opera.com">Opera 9</a>.<hr/>
<small><em>EndoSpider was developed by Arancaytar, aka <a href="mailto:ermarian@gmail.com">Ermarian</a>.
 Visit my site at <a href="http://ermarian.net">The Ermarian Network</a>.</em></small></div>
</body>
</html>

?>