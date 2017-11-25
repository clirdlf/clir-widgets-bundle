<?php

if ($thumbnail_id = get_post_thumbnail_id($pdf->ID)) {
     $output .= '<a class="pdf-link image-link" href="' . get_page_link($publication->ID) . '" title="'.esc_attr(get_the_title($pdf)).'">'.wp_get_attachment_image($thumbnail_id, 'medium').'</a>';
 }
 $output .= '<a href="' . get_page_link($publication->ID). '">';
?>

hi
<a href=""><?php $publication->post_title ?></a>
