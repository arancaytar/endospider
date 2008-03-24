<?php

function form($id) {
  if (!$form = form_build($id)) return "FORM NOT FOUND";
  if ($_SERVER['HTTP_METHOD'] == 'POST') {
    $input = form_check_input($form, $_POST[]);
    $return = form_execute($id, $input);
    if (!headers_sent()) {
      header("Location: ". l($return), 303);
    }
  } else {
    return form_render($form);
  }
}

function form_build($id) {
  $function = 'form_'. $id;
  if (function_exists($function)) return $function();
}

function form_check_input($form, $post) {
  foreach (array_keys($form) as $field) {
    $filtered[$field] = $post[$field];
  }
  return $filtered;
}

function form_execute($id, $input) {
  $function = 'form_'. $id .'_submit';
  if (function_exists($function)) return $function($input);
}

function form_render($form) {
  $out = '<form method="post" action="' + $_SERVER['REQUEST_URI'] + '">';
  $out .= form_render_($form);
  $out .= '</form>';
  return $out;
}

function form_render_($fields, $prefix = array(), $root = '') {
  $out = '';
  $prefix[] = $root;
  foreach ($fields as $id => $field) {
    $render = form_render_field($field, $id, $prefix);
    $out .= $render[0] . form_render_(form_children($field), $prefix, $id) . $render[1];
  }
  return $out;
}

function form_children($field) {
  $children = array();
  foreach ($field as $property => $value) {
    if ($property[0] != '#') $children[$property] = $value; 
  }
  return $children;
}

function form_render_field($field, $id, $prefix) {
  $html_id = '';
  if (count($prefix)) $html_id .= array_shift($prefix);
  if (count($prefix)) $html_id .= "[". implode("][", $prefix) ."]";
  if ($html_id) $html_id .= "[$id]";
  else $html_id = $id;
  $function = 'form_render_field_'. $field['#type'];
  if (function_exists($function)) return $function($field, $html_id); 
}

function form_render_field_text($field, $name) {
  $id = preg_replace('/[\[\]]+/', '-', $name);
  return '<label for="'. $id .'">'. $field['#title'] .'</label><input type="text" id="'. $id .'" name="'. $name .'" />';
}

function form_render_field_submit($field, $id) {
  return '<input type="submit" name="'. $id .'" value="'. $field['#value'] .'" />';
}