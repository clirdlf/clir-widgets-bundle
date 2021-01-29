<?php

function ms_column($atts, $content = '')
{
    extract(shortcode_atts(array(
      'id'         => "",
      'class'      => "",
      'align'      => "",
      'style'      => ""
    ), $atts));

    switch ($style) {
        case '1/1':
            $columnclass='col-md-12';
            break;
        case '1/2':
            $columnclass='col-md-6';
            break;
        case '1/3':
            $columnclass='col-md-4';
            break;
        case '1/4':
            $columnclass='col-md-3';
            break;
        case '1/5':
            $columnclass='col-md-1_5';
            break;
        case '1/6':
            $columnclass='col-md-2';
            break;
        case '2/3':
            $columnclass='col-md-8';
            break;
        case '2/5':
            $columnclass='col-md-2_5';
            break;
        case '3/4':
            $columnclass='col-md-9';
            break;
        case '3/5':
            $columnclass='col-md-3_5';
            break;
        case '4/5':
            $columnclass='col-md-4_5';
            break;
        case '5/6':
            $columnclass='col-md-10';
            break;
        default:
            $columnclass='col-md-12';
    }

    $html = sprintf(
        '<div class="%s %s" id="%s", style="text-align:%s;">%s</div>',
        $class,
        $columnclass,
        $id,
        esc_attr($align),
        $content
    );

    return $html;
}
