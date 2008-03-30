<?php

function page_venn_and($a, $b) {
  $set_a = db_read('endorsement', array('giving'), array('receiving' => $a));
  $set_b = db_read('endorsement', array('giving'), array('receiving' => $b));
  
  $display = array_intersect($set_a, $set_b);
  sort($display);
  $header = array('nation' => t('Nation'), 'received' => t('Endorsements received'));
  $display = db_read('nation', array('nation', 'received'), array('nation' => $display));
  foreach ($display as &$row) $row['nation'] = l('http://nationstates.net/'. $row['nation'], n($row['nation']));
  $page->title = t("Nations endorsing @a and @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = t('There are %num nations in this set', array('%num' => count($display)));
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
  $display = db_read('nation', array('nation', 'received'), array('nation' => $display));
  foreach ($display as &$row) $row['nation'] = l('http://nationstates.net/'. $row['nation'], n($row['nation']));
  $page->title = t("Nations endorsing neither @a nor @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = t('There are %num nations in this set', array('%num' => count($display)));
  $header = array('nation' => t('Nation'), 'received' => t('Endorsements received'));
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
  foreach ($display as &$row) $row['nation'] = l('http://nationstates.net/'. $row['nation'], n($row['nation']));
  $page->title = t("Nations endorsing @a and not @b", array('@a' => n($a), '@b' => n($b)));
  $page->content = t('There are %num nations in this set', array('%num' => count($display)));
  $page->content .= html_table($header, $display);
  return $page;  
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
  $page->content = html_table($header, $row);
  return $page;
} 