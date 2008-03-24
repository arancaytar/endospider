<?php
/*
 * Created on 19.05.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
function page_install()
{
	set_title("Installation Step 1/3: Database Configuration");
	if (file_exists("config.php") && file("config.php"))
	{
		$out=theme('error',t("The installer has detected that the software has already been installed. " .
				"To avoid damaging the previous installation, this installer will not continue until you manually delete the config.php file from the folder. " .
				"Once you have done so, reload this page."));
		return $out;
	}
	if (!$test=fopen("config.php",'w+'))
	{
		$out=theme('error',t("I cannot create or write to the file <strong>config.php</strong>, possibly due to inadequate file permissions. " .
				"You can try to create the file yourself and set its mask to to 0777. If this does not help, contact your hosting provider."));
		return $out;
	}
	$out=theme('paragraph',t("Please enter the hostname of your MySQL server below. If you have a paid web host, your host should have
		told you this hostname and your login info. If you are running your own web and MySQL server, leave it
		as <em>localhost</em>"));
		
	$form=array();
	$out=theme('form');
	
	
	return $out;
}

function form_database()
{
	$form['hostname']=array(
		'#type'=>'textfield',
		'#title'=>t("MySQL Hostname"),
		'#value'=>t("localhost"),
		'#required'=>true,
	);
	$form['user']=array(
		'#type'=>'textfield',
		'#title'=>t("User"),
		'#required'=>true,
	);	
	$form['password']=array(
		'#type'=>'password',
		'#title'=>t("Password"),
		'#required'=>true,
	);
	$form['database']=array(
		'#type'=>'textfield',
		'#title'=>t("Database"),
		'#required'=>true,
	);
	$form['prefix']=array(
		'#type'=>'textfield',
		'#title'=>t("Table Prefix"),
	);
	
	return $form;
}

function form_database_validate($values)
{
	$test=mysql_connect($values['hostname'],$values['user'],$values['password']);
	if (!$test) set_error()
}


function form_database_submit($values)
{
	
}


?>
