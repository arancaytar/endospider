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

function l($path = "", $anchor = "") {
  $url = url($path);
  if (!$anchor) return $url;
  return '<a href="'. $url .'">'. $anchor .'</a>';
}

function nl($nation) {
  return l('http://nationstates.net/'. $nation, n($nation));
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
    'second' => $seconds % 60,
    'minute' => floor($seconds / 60) % 60,
    'hour' => floor($seconds / 3600) % 24,
    'day' => floor($seconds / 86400),
  );
  foreach ($time as $unit => $amount) {
    if ($amount) $out[] = t('%s '. $unit. ($amount > 1 ? 's' : ''), array('%s' => $amount)); 
  }
  return implode(" ", $out);
}
