<?php

function loadAsset($url)
{
  return asset(str_replace('/public', '', $url));
}
