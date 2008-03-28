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

function url($path) {
  if (preg_match('/^[a-z]+:\/\//', $path)) return $path; // absolute
  static $url = "";
  if (!$url) $url = dirname($_SERVER['PHP_SELF']);
  return "$url/$path";
}
