<?php

define('SIG_REGION', 'the_north_pacific');
define('SIG_ELECT', 'ermarian');
define('SIG_I_LABEL', 'usurper');
define('SIG_E_LABEL', 'defender');
require_once 'includes/string.php';
function page_signature() {
  $delegate = db_read('region', array('delegate'), array('region' => SIG_REGION));
  $db = db_read('nation', array('nation', 'received', 'scanned'), array('nation' => array($delegate, SIG_ELECT)));
  $scan = time() - strtotime($db[0]['scanned']);
  if ($scan > 3600) {
    $gb = spider_nation($delegate);
    $lc = spider_nation(SIG_ELECT);
    db_write('nation', $delegate, array('received' => count($gb['endorsements']), 'scanned' => date("Y-m-d H:i:s")), DB_UPDATE);
    db_write('nation', SIG_ELECT, array('received' => count($lc['endorsements']), 'scanned' => date("Y-m-d H:i:s")), DB_UPDATE);
    $db = db_read('nation', array('nation', 'received', 'scanned'), array('nation' => array($delegate, SIG_ELECT)));
    $scan = 0;
  }
  
  $nations[$db[0]['nation']] = $db[0]['received'];
  $nations[$db[1]['nation']] = $db[1]['received'];
  #var_dump($nations);
  $total = $nations[$delegate] + $nations[SIG_ELECT];
  $im = imagecreate(468, 60);
  imagefilledrectangle(
    $im, 0, 0, 
    ceil($nations[$delegate] / $total * 468), 
    60, imagecolorallocate($im, 0, 0, 255)
  );
  imagefilledrectangle($im, 468 - ceil($nations[$delegate] / $total * 468), 0, 468, 60, imagecolorallocate($im, 255, 0, 0));
  imagestring($im, 3, 5, 5, 'Scanned '. strip_tags(interval($scan)) .' ago', imagecolorallocate($im, 240, 220, 0));
  imagestring($im, 5, ceil($nations[SIG_ELECT] / $total * 234), 20, $nations[SIG_ELECT], imagecolorallocate($im, 255, 255, 255));
  imagestring($im, 5, 234 + ceil($nations[$delegate] / $total * 234), 20, $nations[$delegate], imagecolorallocate($im, 0, 0, 0));
  imagestring($im, 5, ceil($nations[SIG_ELECT] / $total * 468), 20, $nations[$delegate] - $nations[SIG_ELECT], imagecolorallocate($im, 240, 220, 0));
  imagestring($im, 3, 10, 40, url_to_display(SIG_ELECT) . ' (' . SIG_E_LABEL . ')', imagecolorallocate($im, 255, 255, 255));
  imagestring($im, 3, 250, 40, url_to_display($delegate) . ' (' . SIG_I_LABEL . ')', imagecolorallocate($im, 0, 0, 0));
  ob_start();
  imagepng($im);
  $out = ob_get_clean();
  $page->content_type = 'png';
  $page->template = 'image';
  $page->content = $out;
  return $page;
}
