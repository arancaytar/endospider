<?php
function string_array_encode($data) {
  foreach($data as $name=>$value) $pieces[]="$name=".urlencode($value);
  return implode("&",$pieces);
}


function http_login($url) {
  static $cookie = FALSE;
  if (!$cookie) {
    $nation = 'ermarian';
    $password = 'RC3016';
    $action = 'http://www.nationstates.net/';
    $method = 'post';
    $args = array(
      'nation'=>$nation,
      'password'=>$password,
      'logging_in'=>1,
    );
    $header = form_submit($action,'POST',$args)->headers['Set-Cookie'];
    preg_match('/pin=[0-9]+;/', $header, $cookie);
    $cookie['Cookie'] = $cookie[0];
  }

  return http($url, $cookie);
}

function form_submit($action,$method='GET',$fields=NULL,$cookies=NULL)
{
  $data=string_array_encode($fields);
  $header=array();
  if ($cookies)
  {
    foreach ($cookies as $name=>$value) $header['Cookie'][]="$name=$value";
    $header['Cookie']=implode(";",$header['Cookie']);
  }
  return http($action,$header,$method,$data);
}


function spider_nation_ejection($nation) {
  $response = http_login("http://www.nationstates.net/page=rcontrol_ejection_estimator/template-overall=none/action=eject/nation=$nation");
  //var_dump($response->data);
  preg_match_all('/would consume (.*?) of your influence./', $response->data, $match);
//  var_dump($match);
  $amounts = $match[1];
  if (count($amounts) == 1) {
    $amounts = array($amounts[0], $amounts[0]);
  }
  $amounts = array_combine(array('ban', 'eject'), $amounts);
  return $amounts;
}



