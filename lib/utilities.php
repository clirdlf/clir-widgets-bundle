<?php
 /**
  * Create an excerpt of an arbitrary length for a given Post
  *
  * @see https://codex.wordpress.org/Function_Reference/get_the_excerpt
  *
  * @param Post $post Post to generate excerpt from
  * @param int  $length Length of the excerpt
  *
  * @return String Excerpt of length $length
  */
  function the_excerpt_max_charlength($post, $charlength)
  {
      $excerpts = get_the_excerpt( get_post($post['ID']));
      $excerpt = $excerpts[0];

      $output = "";
      $charlength++;

      return $excerpts;

     //  if (mb_strlen($excerpt) > $charlength) {
     //      $subex = mb_substr($excerpt, 0, $charlength - 5);
     //      $exwords = explode(' ', $subex);
     //      $excut = - (mb_strlen($exwords[ count($exwords) - 1 ]));
     //      if ($excut < 0) {
     //          $output = mb_substr($subex, 0, $excut);
     //      } else {
     //          $output = $subex;
     //      }
     //      echo '[...]';
     //  }
      //
     //  return $output;
  }

  // TODO: move to utilities
  function local_debug($content)
  {
    $output = "<pre>";
    $output .= var_dump($content);
    $output .= "</pre>";

    return $output;
  }

  // TODO move to utilities
  function clean_category( $category )
  {
    $cat = explode(' ', $category);
    $str = $cat[0];
    $str = strtolower($str);
    return preg_replace('/[^A-Za-z0-9\-]/', '', $str);
  }

  // TODO move to utilities
  function random_image( $category )
  {
    $cc = clean_category($category);
    $path = CLIR_WIDGETS_PLUGIN_PATH . 'lib/images/dlf/' . $cc . '*.{jpg,jpeg,png,gif}';
    $images = glob( $path, GLOB_BRACE );
    $image =  $images[array_rand($images)];
    return plugin_dir_url(__FILE__) . 'images/dlf/' . basename($image);
    // return WP_PLUGIN_URL . '/lib/images/dlf/' . basename($image);
  }
