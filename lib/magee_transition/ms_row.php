<?php

function ms_row($atts, $content = '')
{
    extract(shortcode_atts(array(
      'id'         => "",
      'class'      => "",
      'no_padding' => ""
    ), $atts));

    if ($no_padding == 'yes') {
        $class .= ' no-padding';
    }

    $html = sprintf('<div id="%s" class="%s row">%s</div>', $id, $class, $content);

    return $html;
}
