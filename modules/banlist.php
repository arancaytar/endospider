<?php

function page_banlist() {
  $page->title = 'Checking list of nations';
  $page->content = form('banlist');
  return $page;
}
