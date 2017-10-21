<?php

function add_iframe($initArray)
{
  $initArray['extended_valid_elements'] = "iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]";
  return $initArray;
}
// don't filter iframe element in visual mode
add_filter('tiny_mce_before_init', 'add_iframe');
