<?php
define('DB_REPLACE', 'REPLACE');
define('DB_INSERT', 'INSERT');
define('DB_UPDATE', 'UPDATE');
define('DB_DELETE', 'DELETE');

function db_write($type, $key, $attributes, $action = DB_INSERT) {
  $table = DB_PREFIX . $type;
  $schema = db_schema($type);
  if (!is_array($key) || (is_array($schema['key']) && !is_array($key[0]))) {
    $key = array($key);
  }
  switch ($action) {
    case DB_REPLACE:
    case DB_INSERT:
      $sql = "$action INTO `$table` (`". implode("`, `", $schema['fields']) ."`) VALUES ";
      $records = array();
      foreach ($key as $id) {
        $record = is_array($schema['key']) ? $id + $attributes : array($schema['key'] => $id) + $attributes;
        $cols = array();
        foreach ($schema['fields'] as $field) {
          $cols[] = "'". db_string($record[$field]) ."'";
        }
        $records[] = "(". implode(", ", $cols) .")";
      }
      $sql .= implode(", ", $records);
      break;
    case DB_UPDATE:
      $sql = "UPDATE `$table` ";
      foreach ($attributes as $col => $value) {
        if (in_array($col, $schema['fields'])) $update[] = "`$col` = '$value'";
      }
      $sql .= 'SET '. implode(", ", $update);
      if (is_array($schema['key'])) {
        $schema['key'] = 'CONCAT(`'. implode("`,'-',`", $schema['key']) .'`)';
        foreach($key as &$id) $id = implode("-", $id);
      }
      foreach ($key as &$id) $id = db_string($id);
      $sql .= ' WHERE '. $schema['key'] ." IN ('". implode("', '", $key) ."')";
      break;
    case DB_DELETE:
      $sql = "DELETE FROM `$table` ";
      if (is_array($schema['key'])) {
        $schema['key'] = 'CONCAT(`'. implode("`,'-',`", $schema['key']) .'`)';
        foreach($key as &$id) $id = implode("-", $id);
      }
      foreach ($key as &$id) $id = db_string($id);
      $sql .= 'WHERE '. $schema['key'] ." IN ('". implode("', '", $key) ."')";
      break;
  }
  db_query($sql);
  
}

function db_read($record, $fields, $criteria) {
  if (!$schema = db_schema($record)) return;
  $table = DB_PREFIX . $record;
  foreach ($fields as $i=>$field) {
    if (!in_array($field, $schema['fields'])) {
      unset($fields[$i]);
    }
  }
  foreach ($criteria as $i=>$field) {
    if (!in_array($i, $schema['fields'])) {
      unset($criteria[$i]);
    }
  }
  $fields = implode('`, `', $fields);
  $sql = "SELECT `$fields` FROM `$table` ";
  foreach ($criteria as $attribute => $value) {
    if (is_array($value)) foreach ($value as &$v) $v = db_string($v);
    $c[] = "`$attribute` ". (is_array($value) ? " IN ('". implode("', '", $value) ."')" : " = '". db_string($value) ."'"); 
  }
  $sql .= "WHERE ". implode(" AND ", $c);
  $result = db_query($sql);
  while ($row = db_fetch_array($result)) $rows[] = $row;
  return count($rows) > 1 ? $rows : $rows[0];
}

function db_schema($record = NULL) {
  static $schema = array(
    'nation' => array(
      'fields' => array(
        'nation', 'region', 'received', 'indexed', 'scanned',
      ),
      'key' => 'nation',
    ),
    
    'region' => array(
      'fields' => array(
        'region', 'size', 'delegate', 'scan_started', 'scan_ended',
      ),
      'key' => 'region',
    ),
    
    'endorsement' => array(
      'fields' => array(
        'giving', 'receiving', 'region'
      ),
      'key' => array('giving', 'receiving'),
    ),
  );
  
  return $record ? $schema[$record] : array_keys($schema);
} 

function db_query($sql, $args = array()) {
  static $link;
  if (is_array($args)) {
    array_unshift($args, $sql);
  } else $args = func_get_args();
  //var_dump($sql);
  $sql = call_user_func_array('sprintf', $args);
  //var_dump($sql);
  $sql = preg_replace('/{([a-z]+)}/e', 'db_prefix("$1")', $sql);
  //var_dump($sql);
  if (!$link) $link = db_connect_();
  return mysql_query($sql);
}

function db_fetch_array($res) {
  $ar = mysql_fetch_array($res);
  if (count($ar) == 2) {
    $ar = $ar[0];
  }
  return $ar;
}

function db_connect_() {
  $link = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
  @mysql_select_db(MYSQL_DATABASE);
  return $link;
}

function db_string($string) {
  return str_replace(array('\\', '\''), array('\\\\', '\\\''), $string); 
}

function db_prefix($string) {
  if (db_schema($string)) return '`'. DB_PREFIX . $string .'`';
}
