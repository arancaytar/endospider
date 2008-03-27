<?php

define('VERSION', '3.0alpha');
define('VERSION_RV', 252);

require_once "includes/alias.php";
require_once "includes/auth.php";
require_once "includes/database.php";
require_once "includes/form.php";
require_once "includes/http.php";
require_once "includes/locale.php";
require_once "includes/spider.php";
require_once "includes/status.php";

require_once "modules/auth.php";
require_once "modules/error.php";
require_once "modules/gather.php";
require_once "modules/overview.php";
require_once "modules/region.php";
require_once "modules/venn.php";
require_once "modules/tart.php";

require_once "template/html.php";

include_once 'config.php';

function main() {
  $page = alias_execute($_GET['q']);
  if (!is_object($page)) $page = (object)(array('content' => $page));
  if (!$page->content_type) $page->content_type = 'application/xhtml+xml';
  if (!$page->code) $page->code = 200;
  if (!$page->template) $page->template = 'html';
  header("Content-type: ". $page->content_type, $page->code);
  if (!empty($page->template)) {
    $function = 'template_'. $page->template;
    print $function($page);
  } else print $page->content;
}

main();

?>
