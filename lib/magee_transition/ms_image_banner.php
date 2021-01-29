<?php

function ms_image_banner($atts, $content='')
{
    extract(shortcode_atts(array(
      'id' =>'',
      'class' =>'',
      'horizontal_align' =>'center', //left/center/right
      'vertical_align' => 'middle',  //top/middle/bottom
      'zoom_effect' => 'in', // in/out
      'image' =>'',
      'link'=>'',
      'target'=>'_blank'
    ), $atts));

    $class .= ' magee-image-banner';
    $text_align = '';
    switch ($horizontal_align) {
        case "center":
            $text_align = 'text-center';
            break;
        case "left":
            $text_align = 'text-left';
            break;
        case "right":
            $text_align = 'text-right';
            break;
    }
    $html = '';
    if ($image != '') :
        $image = $link == ''? '<img src="'.esc_url($image).'" class="feature-img">':'<a href="'.esc_url($link).'" target="'.esc_attr($target).'"><img src="'.esc_url($image).'" class="feature-img">';

        $html = '<div class="img-box figcaption-'.$vertical_align.' '.$text_align.' img-zoom-'.$zoom_effect.'">
                                                '.$image.'
                                                <div class="img-overlay">
                                                    <div class="img-overlay-container">
                                                        <div class="img-overlay-content">
                                                          '.$content.'
                                                        </div>
                                                    </div>
                                                </div>  '.($link == ''?'':'</a>').'
                                            </div>';

        $html = sprintf('<div id="%s" class="%s">%s</div>', $id, $class, $html);
    endif;

    return $html;
}
