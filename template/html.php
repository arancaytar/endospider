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
  $menu = array('' => t('Overview'), 'gather' => t('Gather data'));
  if (auth()) $menu['logout'] = t('Log out');
  $out = '<ul>';
  foreach ($menu as $link => $text) {
    $out .= '<li>'. l($link, $text) .'</li>';
  }
  $out .= '</ul>';
  return $out;
}

function template_html($page) {
  $defaults = array('title' => '', 'start' => '', 'prev' => '', 'next' => '');
  foreach ($defaults as $var => $val) $$var = $val;
	foreach ($page as $var => $val) $$var = $val;
  $menu = main_menu(isset($_GET['q']) ? $_GET['q'] : '');
  $messages = message();
	include_once('template/page.tpl.php');
} 


function html_table($header, $rows) {
  $out = '<table>';
  $out .= '<thead><tr>'. html_table_header_($header) .'</tr></thead>';
  $out .= '<tbody>';
  
  foreach ($rows as $row) {
    $out .= '<tr>';
    foreach ($header as $key => $title) {
      $out .= '<td>'. $row[$key] .'</td>';
    }
    $out .= '</tr>';
  }
  $out .= '</tbody>';
  $out .= '</table>';
  return $out;
}

function html_table_header_($header) {
  $out = '';
  foreach ($header as $cell) {
    if (!is_array($cell)) $cell = array('data' => $cell);
    $out .= '<th class="'. (isset($cell['class']) ? $cell['class'] : '') .'">'. $cell['data'] .'</th>';
  }
  return $out;
}
?>
