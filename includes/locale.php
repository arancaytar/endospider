<?php

function t($text, $placeholders = array()) {
  foreach ($placeholders as $key => &$value) {
    switch ($key[0]) {
      case '@':
        $value = html_entities($value);
        break;
      case '%':
        $value = '<strong>'. html_entities($value) .'</strong>';
        break;
    }
  }
  return str_replace(array_keys($placeholders), $placeholders, $text);
}
