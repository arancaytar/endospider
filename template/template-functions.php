<?php
/*
 * Created on 19.05.2007
 *
 * To change the template for this generated file go to
 * Window - Preferences - PHPeclipse - PHP - Code Templates
 */
 function pager($count, $parameters) {
		if (!$parameters) return false;
		$st = 0;
		$limit=25;
		foreach($_GET as $name=>$value) $$name=$value;
		if ($count<=$limit) return false;
		$pages = ceil($count/$limit);
		echo "Pages: ";
		for ($i=1;$i<=$pages;$i++) {
			$start = ($i-1)*$limit;
			echo ($start!=$st)?"<a href='?$parameters&amp;st=$start'>":"<strong>";
			echo $i;
                        echo ($start!=$st)?"</a> ":"</strong> ";
		}
}

function main_menu($url) {
}

function template_html($page) {
	foreach ($page as $var => $val) $$var = $val;
	include_once('template/page.tpl.php');
} 
?>