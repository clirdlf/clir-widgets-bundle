<?php

function ms_custom_box($atts)
{
    extract(shortcode_atts(array(
          'id'                    =>'',
          'class'                 =>'',
          'fixed_background'      =>'',
          'background_position'   =>'',
          'padding'               =>'',
          'backgroundimage'       =>''
      ), $atts));

    if (is_numeric($padding)) {
          $padding = $padding.'px';
    }

    if ($fixed_background == 'yes') :
        $fixed_background = 'fixed';
    else :
        $fixed_background = '';
    endif;

    $uniqid = uniqid('custom_box-');

    $textstyle = sprintf(' .custom-box-1 {padding: %s; background-image: url(%s);background-attachment: %s;background-position: %s;} ',$padding,$backgroundimage,$fixed_background,$background_position);
    $styles = sprintf( '<style type="text/css" scoped="scoped">%s </style>', $textstyle);
    $html = sprintf(' %s<div class="custom-box-1 %s" id="%s">%s </div>',$styles,$class,$id,do_shortcode( Magee_Core::fix_shortcodes($content)));

    return $html;
}
