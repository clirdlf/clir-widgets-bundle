<?php
/**
 * This only works because font-awesome is required in the Hello theme
 *
 */

/**
 * [ms_icon description]
 * @param  [type] $atts [description]
 * @return [type]       [description]
 */
function ms_icon($atts)
{
    extract(shortcode_atts(array(
      'id'         => "",
      'class'      => "",
      'icon'      => "",
      'color'      => "",
      'size'      => "",
      'icon_box'      => "",
    ), $atts));

    if (is_numeric($size)) {
        $size = $size . 'px';
    }

    $html = '';
    $css_style = '';
    $uniqueid = ' magee-fa-icon icon-boxed';

    if ($size) {
        $css_style .= 'font-size:' . $size . ';';
    }

    if ($icon_box == 'yes') {
        $icon = $uniqueid;
        if ($color) {
            $css_style .= 'background:' . $color . ';';
        }
        if ($icon != '') {
            $html = sprintf(
                '<i id="%s" class="%s fa %s" style="%s"></i>',
                $id,
                $class,
                $icon,
                $css_style
            );
        }
    } else {
        if ($color) {
            $css_style .= 'color:'.$color.';';
        }
        if ($icon != '') {
            $html = sprintf(
                '<i id="%s" class="%s fa %s" style="%s"></i>',
                $id,
                $class,
                $icon,
                $css_style
            );
        }
    }
    return $html;
}
