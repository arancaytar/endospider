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
      foreach ($key as $id) {
        $record = is_array($schema['key']) ? $id + $attributes : array($schema['key'] => $id) + $attributes;
        foreach ($schema['fields'] as $field) {
          $cols[] = "'". $record[$field] ."'";
        }
        $records[] = "(". implode(", ", $cols) .")";
      }
      $sql .= implode(", ", $records);
      break;
    case DB_UPDATE:
      $sql = "UPDATE `$table` ";
      foreach ($attributes as $col => $value) {
        $update[] = "`$col` = '$value'";
      }
      $sql .= 'SET '. implode(", ", $update);
      if (is_array($schema['key'])) {
        $schema['key'] = 'CONCAT(`'. implode("`,'-',`", $schema['key']) .'`)';
        foreach($key as $i=>$id) $key = implode("-", $id);
      }
      $sql .= 'WHERE '. $schema['key'] ." IN ('". implode("', '", $key) ."')";
      break;
    case DB_DELETE:
      $sql = "DELETE FROM `$table` ";
      if (is_array($schema['key'])) {
        $schema['key'] = 'CONCAT(`'. implode("`,'-',`", $schema['key']) .'`)';
        foreach($key as $i=>$id) $key = implode("-", $id);
      }
      $sql .= 'WHERE '. $schema['key'] ." IN ('". implode("', '", $key) ."')";
      break;
  }
  db_query($sql);
  
}

function db_read($record, $criteria) {
  $table = DB_PREFIX . $type;
  $sql = "SELECT * FROM `$table` ";
  foreach ($criteria as $attribute => $value) {
    $c[] = "`$attribute` ". (is_array($value) ? " IN ('". implode("', '", $value) ."')" : " = '$value'"); 
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
        'nation', 'region'
      ),
      'key' => 'nation',
    ),
    
    'region' => array(
      'fields' => array(
        'region', 'size', 'delegate' 
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

function db_query($sql) {
  static $link;
  if (!$link) $link = db_connect_();
  mysql_query($sql);
}

function db_connect_() {
  $link = @mysql_connect(MYSQL_HOST, MYSQL_USER, MYSQL_PASS);
  @mysql_select_db(MYSQL_DATABASE);
  return $link;
}
