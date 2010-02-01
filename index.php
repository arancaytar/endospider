<?php

define('VERSION', '3.0alpha');
define('VERSION_RV', 367);

require_once "includes/alias.php";
require_once "includes/auth.php";
require_once "includes/database.php";
require_once "includes/form.php";
require_once "includes/http.php";
require_once "includes/json.php";
require_once "includes/locale.php";
require_once "includes/spider.php";
require_once "includes/status.php";
require_once "includes/xml.php";

require_once "modules/auth.php";
require_once "modules/banlist.php";
require_once "modules/error.php";
require_once "modules/gather.php";
require_once "modules/overview.php";
require_once "modules/ranking.php";
require_once "modules/region.php";
require_once "modules/relations.php";
require_once "modules/signature.php";
require_once "modules/tart.php";
require_once "modules/venn.php";
require_once "modules/xml.php";

require_once "template/html.php";

include_once 'config.php';

function main() {
  $page = alias_execute(isset($_GET['q']) ? $_GET['q'] : '');
  if (!is_object($page)) $page = (object)(array('content' => $page));
  if (empty($page->content_type)) $page->content_type = 'xhtml';
  if (empty($page->code)) $page->code = 200;
  if (empty($page->template)) $page->template = 'html';
  http_content_type($page->content_type, $page->code);
  ob_start('ob_gzhandler');

  if (!empty($page->template)) {
    $function = 'template_'. $page->template;
  }
  if (function_exists($function)) {
    print $function($page);
  } else print $page->content;
  ob_end_flush();
}

main();

?>
