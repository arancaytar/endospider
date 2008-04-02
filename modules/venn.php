<?php

function page_venn_and($a, $b) {
  $set_a = db_read('endorsement', array('giving'), array('receiving' => $a));
  $set_b = db_read('endorsement', array('giving'), array('receiving' => $b));
  
  $display = array_intersect($set_a, $set_b);
  sort($display);
  $header = array('nation' => t('Nation'), 'received' => t('Endorsements received'));
  $display = db_read('nation', array('nation', 'received'), array('nation' => $display));
  foreach ($display as &$row) $row['nation'] = nl($row['nation']);
  $page->title = t("Nations endorsing @a and @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = '<p>'. t('Venn diagram subsets: <ul><li>!all</li><li>!neither</li><li>!left</li><li>!right</li><li>!both</li></ul>', 
    array(
      '!all' => l('venn/'. $a .'/'. $b, 'all'),
      '!neither' => l('venn/neither/'. $a .'/'. $b, 'neither'),
      '!both' => l('venn/and/'. $a .'/'. $b, 'both'),
      '!left' => l('venn/left/'. $a .'/'. $b, n($a) .' and not '. n($b)),
      '!right' => l('venn/right/'. $a .'/'. $b, n($b) .' and not '. n($a)),
    )
  ) .'</p>';
  $page->content .= t('There are %num nations in this set', array('%num' => count($display)));
  $page->content .= html_table($header, $display);
  return $page;
}

function page_venn_neither($a, $b) {
  $region = array_unique(db_read('nation', array('region'), array('nation' => array($a, $b))));
  if (count($region) > 1) {
    return page_error_404();
  }
  $set_total = db_read('nation', array('nation'), array('region' => $region));
  $set_either = db_read('endorsement', array('giving'), array('receiving' => array($a, $b)));
  
  $display = array_diff($set_total, $set_either);
  sort($display);
  $display = db_read('nation', array('nation', 'received', 'flag', 'influence', 'given', 'active'), array('nation' => $display));
  foreach ($display as &$row) {
    $row['nation'] = nl($row['nation']);
    $row['image'] = flag($row['flag']);
    $row['active'] = interval($row['active']) .' ago';
  }
  $page->title = t("Nations endorsing neither @a nor @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = '<p>'. t('Venn diagram subsets: <ul><li>!all</li><li>!neither</li><li>!left</li><li>!right</li><li>!both</li></ul>', 
    array(
      '!all' => l('venn/'. $a .'/'. $b, 'all'),
      '!neither' => l('venn/neither/'. $a .'/'. $b, 'neither'),
      '!both' => l('venn/and/'. $a .'/'. $b, 'both'),
      '!left' => l('venn/left/'. $a .'/'. $b, n($a) .' and not '. n($b)),
      '!right' => l('venn/right/'. $a .'/'. $b, n($b) .' and not '. n($a)),
    )
  ) .'</p>';
  $page->content .= '<p>'. t('There are %num nations in this set', array('%num' => count($display))) .'</p>';
  $header = array('nation' => t('Nation'), 'received' => t('Endorsements received'), 'flag' => t('Flag'), 'influence' => t('Influence'), 'given' => t('Given'),
    'active' => t('Last active'));
  $page->content .= html_table($header, $display);
  return $page;
}

function page_venn_left($a, $b) {
  $set_a = db_read('endorsement', array('giving'), array('receiving' => $a));
  $set_b = db_read('endorsement', array('giving'), array('receiving' => $b));
  $display = array_diff($set_a, $set_b);
  $header = array('nation' => t('Nation'), 'received' => t('Endorsements received'));
  sort($display);
  $display = db_read('nation', array('nation', 'received'), array('nation' => $display));
  foreach ($display as &$row) $row['nation'] = nl($row['nation']);
  $page->title = t("Nations endorsing @a and not @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = '<p>'. t('Venn diagram subsets: <ul><li>!all</li><li>!neither</li><li>!left</li><li>!right</li><li>!both</li></ul>', 
    array(
      '!all' => l('venn/'. $a .'/'. $b, 'all'),
      '!neither' => l('venn/neither/'. $a .'/'. $b, 'neither'),
      '!both' => l('venn/and/'. $a .'/'. $b, 'both'),
      '!left' => l('venn/left/'. $a .'/'. $b, n($a) .' and not '. n($b)),
      '!right' => l('venn/right/'. $a .'/'. $b, n($b) .' and not '. n($a)),
    )
  ) .'</p>';
  $page->content .= '<p>'. t('There are %num nations in this set', array('%num' => count($display))) .'</p>';
  $page->content .= html_table($header, $display);
  return $page;  
}

function page_venn_right($a, $b) {
  return page_venn_left($b, $a);
}

function page_venn($a, $b) {
  if ($a == $b) return page_error_404();
  $region = db_read('nation', array('region'), array('nation' => array($a, $b)));
  if (count(array_unique($region)) > 1) return page_error_404();
  $region = $region[0];
  $total = db_read('nation', array('nation', 'active'), array('region' => $region));
  $set_a = db_read('endorsement', array('giving'), array('receiving' => $a));
  $set_b = db_read('endorsement', array('giving'), array('receiving' => $b));
  $header = array(
    'nation' => t('Nation'),
    'set' => t('Endorses'),
    'active' => t('Last active'),
  );
  $label = array(t('Neither'), nl($b), nl($a), t('Both'));
  foreach ($total as $n) {
    $nation = $n['nation'];
    $set = in_array($nation, $set_a) * 2;
    $set += in_array($nation, $set_b);
    $row[$nation] = array(
      'nation' => nl($nation),
      'set' => $label[$set],
      'active' => interval($n['active']) . ' ago',
    );
  }
  ksort($row);
  $page->title = t('Relations with @a or @b', array('@a' => n($a), '@b' => n($b)));
  $page->content = '<p>'. t('Venn diagram subsets: <ul><li>!all</li><li>!neither</li><li>!left</li><li>!right</li><li>!both</li></ul>', 
    array(
      '!all' => l('venn/'. $a .'/'. $b, 'all'),
      '!neither' => l('venn/neither/'. $a .'/'. $b, 'neither'),
      '!both' => l('venn/and/'. $a .'/'. $b, 'both'),
      '!left' => l('venn/left/'. $a .'/'. $b, n($a) .' and not '. n($b)),
      '!right' => l('venn/right/'. $a .'/'. $b, n($b) .' and not '. n($a)),
    )
  ) .'</p>';
  $page->content .= html_table($header, $row);
  return $page;
} 