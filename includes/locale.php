<?php

function t($text, $placeholders = array()) {
  foreach ($placeholders as $key => &$value) {
    switch ($key[0]) {
      case '@':
        $value = html_entities($value);
        break;
      case '%':
        $value = '<strong>'. htmlentities($value) .'</strong>';
        break;
    }
  }
  return str_replace(array_keys($placeholders), $placeholders, $text);
}

function l($path = "") {
  static $url = "";
  if (!$url) $url = dirname($_SERVER['PHP_SELF']);
  return $url . $path;
}
