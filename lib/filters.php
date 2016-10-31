<?php

// custom filters for display-posts shortcodes
// see http://www.billerickson.net/code/remove-link-title-display-posts-shortcode/

function clir_category_link( $title_tag, $original_atts )
{
  // https://github.com/billerickson/display-posts-shortcode/blob/master/display-posts-shortcode.php#L527-L544
  // $return = '<h2 class="display-posts-title"><a href="#">' . $shortcode_title . '</a></' . $title_tag . '>' . "\n";
  $return = $title_tag;


  // rebuild output? http://www.billerickson.net/code/remove-link-title-display-posts-shortcode/
  // $output = '<' . $inner_wrapper . ' class="' . implode( ' ', $class ) . '">' . $image . $title . $date . $author . $excerpt . $content . '</' . $inner_wrapper . '>';
  
  // echo '<pre>';
  // echo ($title_tag);
  // echo '</pre>';
  // $return .= $inner . $close;
  return $return;

}

add_filter('display_posts_shortcode_title_tag', 'clir_category_link');
