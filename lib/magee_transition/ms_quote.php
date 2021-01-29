<?php

function ms_quote($atts)
{
    extract(shortcode_atts(array(
          'id' =>'',
          'class' =>'',
          'cite' =>'',
          'url' =>''
        ), $atts));

    $cite_link = '';
    if (esc_url($url) && esc_attr($cite)) {
        $cite_link = '<cite><a href="' . $url . '" target="_blank">'.$cite.'</a></cite>';
    }
    $html ='<div class="magee-blockquote '.esc_attr($class).'" id="'.esc_attr($id).'">';
    $html .='<blockquote><p>'.do_shortcode(Magee_Core::fix_shortcodes($content)).'</p>';
    $html .= '<footer>'.$cite_link.'</footer>' ;
    $html .='</blockquote>';
    $html .='</div>';
    return $html;
}
