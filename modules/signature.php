<?php

function page_signature() {
  $db = db_read('nation', array('nation', 'received', 'scanned'), array('nation' => array('great_bights_mum', 'lewis_and_clark')));
  $scan = time() - strtotime($db[0]['scanned']);
  if ($scan > 3600) {
    $gb = spider_nation('great_bights_mum');
    $lc = spider_nation('lewis_and_clark');
    db_write('nation', 'great_bights_mum', array('received' => count($gb['endorsements']), 'scanned' => date("Y-m-d H:i:s")), DB_UPDATE);
    db_write('nation', 'lewis_and_clark', array('received' => count($lc['endorsements']), 'scanned' => date("Y-m-d H:i:s")), DB_UPDATE);
    $db = db_read('nation', array('nation', 'received', 'scanned'), array('nation' => array('great_bights_mum', 'lewis_and_clark')));
    $scan = 0;
  }
  
  $nations[$db[0]['nation']] = $db[0]['received'];
  $nations[$db[1]['nation']] = $db[1]['received'];
  $total = $nations['great_bights_mum'] + $nations['lewis_and_clark'];
  $im = imagecreate(468, 60);
  imagefilledrectangle(
    $im, 0, 0, 
    ceil($nations['great_bights_mum'] / $total * 468), 
    60, imagecolorallocate($im, 0, 0, 255)
  );
  imagefilledrectangle($im, 468 - ceil($nations['lewis_and_clark'] / $total * 468), 0, 468, 60, imagecolorallocate($im, 255, 0, 0));
  imagestring($im, 3, 5, 5, 'Scanned '. strip_tags(interval($scan)) .' ago', imagecolorallocate($im, 240, 220, 0));
  imagestring($im, 5, ceil($nations['great_bights_mum'] / $total * 234), 20, $nations['great_bights_mum'], imagecolorallocate($im, 255, 255, 255));
  imagestring($im, 5, 234 + ceil($nations['lewis_and_clark'] / $total * 234), 20, $nations['lewis_and_clark'], imagecolorallocate($im, 0, 0, 0));
  imagestring($im, 5, ceil($nations['great_bights_mum'] / $total * 468), 20, $nations['lewis_and_clark'] - $nations['great_bights_mum'], imagecolorallocate($im, 240, 220, 0));
  imagestring($im, 4, 10, 40, 'Great Bights Mum', imagecolorallocate($im, 255, 255, 255));
  imagestring($im, 4, 350, 40, 'Lewis & Clark', imagecolorallocate($im, 0, 0, 0));
  ob_start();
  imagepng($im);
  $out = ob_get_clean();
  $page->content_type = 'png';
  $page->template = 'image';
  $page->content = $out;
  return $page;
}
