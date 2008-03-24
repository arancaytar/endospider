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

function main() {
  ob_start('ob_gzhandler');
  $page = alias_execute($_GET['q']);
  header("Content-type: ". $page->content_type);
  if (!empty($page->template)) {
    $function = 'template_'. $page->template;
    print $function($page);
  } else print $page->content;
  ob_end();
}

main();

?>