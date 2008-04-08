<?php

function t($text, $placeholders = array()) {
  foreach ($placeholders as $key => &$value) {
    switch ($key[0]) {
      case '@':
        $value = htmlentities($value);
        break;
      case '%':
        $value = '<strong>'. htmlentities($value) .'</strong>';
        break;
    }
  }
  return str_replace(array_keys($placeholders), $placeholders, $text);
}

function n($name, $display = TRUE) {
  return $display ? ucwords(str_replace('_', ' ', $name)) : strtolower(str_replace(' ', '_', $name));
}

function l($path = "", $anchor = "", $attributes = array()) {
  static $valid = array('class', 'href', 'rel', 'id', 'style', 'title');
  if ($path == $_GET['q']) {
    return "<strong>$anchor</strong>";
  }
  $default = array('href' => url($path));
  foreach ($valid as $attr) {
    if ($attributes[$attr]) $default[$attr] = $attributes[$attr];
  } 
  if (!$anchor) return $default['href'];
  foreach ($default as $attr => $value) {
    $set[] = $attr .'="'. $value .'"';
  }
  return '<a '. implode(' ', $set) .'>'. $anchor .'</a>';
}

function nl($nation) {
  return l('http://nationstates.net/'. $nation, n($nation), array('rel' => 'nation-link', 'class' => 'link-'. $nation));
}

function rl($region) {
  return l('region/'. $region, n($region), array('rel' => 'region-link', 'class' => 'link-'. $region));
}

function url($path) {
  if (preg_match('/^[a-z]+:\/\//', $path)) return $path; // absolute
  static $url = "";
  if (!$url) $url = dirname($_SERVER['PHP_SELF']);
  return "$url/$path";
}

function interval($seconds) {
  $seconds = floor($seconds);
  $time = array(
    'day' => floor($seconds / 86400),  
    'hour' => floor($seconds / 3600) % 24,
    'minute' => floor($seconds / 60) % 60,
    'second' => $seconds % 60,
  );
  $out = array();

  foreach ($time as $unit => $amount) {
    if ($amount) $out[] = t('%s '. $unit. ($amount > 1 ? 's' : ''), array('%s' => $amount)); 
  }
  return implode(" ", $out);
}

function flag($flag) {
  return '<img class="flag" src="http://www.nationstates.net/images/flags/'. $flag .'" />';
}
