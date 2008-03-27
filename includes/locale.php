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

function l($path = "", $anchor = "") {
  $url = url($path);
  if (!$anchor) return $url;
  return '<a href="'. $url .'">'. $anchor .'</a>';
}

function url($path) {
  static $url = "";
  if (!$url) $url = dirname($_SERVER['PHP_SELF']);
  return "$url/$path";
}
