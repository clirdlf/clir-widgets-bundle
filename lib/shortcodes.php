<?php

/**
 * Used in DLF theme; couldn't find what plugin contained this so I made one.
 * Updated for Bootstrap
 *
 * @see http://getbootstrap.com/css/#grid-example-mixed-complete
 *
 * @return String div with clearfix CSS added (including clearing XS cols if the
 * height doesnt match)
 */
function clir_clearfix()
{
  return '<div class="clearfix visible-xs-block"></div>';
}

/**
 * Display deadline date
 *
 * @param array $attr Shortcode attributes
 *
 * @return string HTML decorated date
 */
 function deadline($attr, $content = null)
 {
   $a = shortcode_atts(
     array(
       'date'          => '',
       'program'       => '',
       'message'       => '',
       'after_message' => 'This year\'s deadline has passed.'
     ), $attr);

     $output = "";

     return $output;


 }

/**
 * Hide email from Spam Bots using a Shortcode
 *
 * @param array  $atts    Shortocde attributes (not used)
 * @param string $content The shortcode content (should be an email address)
 *
 * @return string An obfuscated email address
 */
 function hide_email( $atts, $content = null)
 {
   // guard for accidental wrap
   if(! is_email($content)) {
     return;
   }

   return '<a href="mailto:"' . antispambot($content) . '">' . antispambot($content) . '</a>';
 }

/**
 * Display DLF menu items
 *
 * @param array $atts Shortcode attributes
 *
 * @return string HTML content
 */
 function dlf_menu_entry ( $attr )
 {
   $a = shortcode_atts(
     array(
       'link'  => '/',
       'icon'  => 'fa fa-ban',
       'title' => ''
     ), $attr);

     $output = <<<EOT
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-6 home-iconmenu">
  <a href="{$a['link']}"> <i class="{$a['icon']}"></i>
    <h4>{$a['title']}</h4>
  </a>
</div>
EOT;

    return $output;

 }

/**
* Shortcode for displaying report convers
*
* @see https://codex.wordpress.org/Function_Reference/get_pages
* @see https://wordpress.org/plugins/pdf-image-generator/developers/
*
* @params Array $attr Array of options for get_pages
*
* @return String div with n reports
*/
function clir_reports_view( $attr )
{
  $a = shortcode_atts(
    array(
      'parent'      => 240,
      'count'       => '',
      'sort_order'  => 'desc',
      'sort_column' => 'post_date',
    ), $attr);

    $args = array(
      'sort_order' => $a['sort_order'],
      'sort_column' => $a['sort_column'],
      'hierarchical' => 1,
      'exclude' => '',
      'include' => '',
      'meta_key' => '',
      'meta_value' => '',
      'authors' => '',
      'child_of' => 0,
      'parent' => $a['parent'],
      'exclude_tree' => '',
      'number' => $a['count'],
      'offset' => 0,
      'post_type' => 'page',
      'post_status' => 'publish'
    );

    $pages = get_pages($args);

    $output = '<div class="row">';

    $fadeActions = ['fadeInLeft', 'fadeInDown', 'fadeInRight'];

    foreach($pages as $page) {
      $output .= '<div class="col-sm-6 col-md-4">';
      $output .= '<div class="report-cover magee-animated animated '. $fadeActions[array_rand($fadeActions, 1)] . '" data-animationduration="0.9" data-animationtype="fadeInLeft" data-imageanimation="no" style="visibility: visible; animation-duration: 0.9s;">';
      $output .= '<div class="img-frame rounded">';
      $output .= '<div class="img-box figcaption-middle text-center fade-in">';

      // $image = get_attached_media('application/pdf', $page->ID);

      $images = get_attached_media('application/pdf', $page->ID);

      $pdf_id = reset($images);

      if ( $thumbnail_id = get_post_thumbnail_id( $pdf_id ) ) {
        $output .= '<a class="pdf-link image-link" href="' . get_page_link($page->ID) . '" title="'.esc_attr( get_the_title( $pdf_id ) ).'">'.wp_get_attachment_image ( $thumbnail_id, 'medium' ).'</a>';
        // $output .= '<a class="pdf-link image-link" href="'. wp_get_attachment_url( $pdf_id ).'" title="'.esc_attr( get_the_title( $pdf_id ) ).'">'.wp_get_attachment_image ( $thumbnail_id, 'medium' ).'</a>';
      }



      $output .= '<a href="' . get_page_link($page->ID). '">';
      // $output .= '<div class="img-overlay dark">';
      // $output .= '<div class="img-overlay-container">';
      // $output .= '<div class="img-overlay-content">';
      $output .= '<h3>'. $page->post_title .'</h3>';
      // $output .= '<i class="fa fa-link" style="visibility: visible;"></i>
      //   </div>
      // </div>';

      $output .= '</a>';
      $output .= '</div>'; // img-box
      $output .= '</div>'; // img-frame
      $output .= '</div>'; // animated
      $output .= '</div>'; // col-sm-6 col-md-4
    }

    $output .= '</div>'; // row

    return $output;
  }

  /**
  * For embedding the DLF Community Calendar (managed in Google Calendar)
  *
  * @return String embed code for the community calendar. Can be overridden for
  * other Google calendars
  */
  function community_calendar( $atts )
  {
    $a = shortcode_atts(array(
      'width'       => '800',
      'height'      => '600',
      'src'         => 'g2hval0pee3rmrv4f3n9hp9cok@group.calendar.google.com',
      'bgcolor'     => '#FFFFFF',
      'color'       => '#2F6309',
      'ctz'         => 'America/New_York',
      'frameborder' => 0,
      'scrolling'   => 'no',
      'style'       => 'border-width:0'
    ), $atts);

    $url = 'https://calendar.google.com/calendar/embed?height=' . $a['height'];
    $url .= '&amp;wkst=1&amp;bgcolor=' . urlencode($a['bgcolor']);
    $url .= '&amp;src=' . urlencode($a['src']);
    $url .= '&amp;color=' . urlencode($a['color']) . '&amp;ctz=' . urlencode($a['ctz']);

    $iframe = "<iframe src='{$url}' style='{$a['style']}' width='{$a['width']}' height='{$a['height']}' frameborder='{$a['frameborder']}' scrolling='{$a['scrolling']}'></iframe>";
    $iframe .= "<p style='font-size: smaller;'>Maintained by the <a href='https://www.diglib.org/opportunities/calendar/'>Digital Library Federation</a></p>";

    return $iframe;
}



function dlf_post( $atts )
{
  //TODO: get an array of random images to get thumbs for
  $a = shortcode_atts(array(
    'category' => 'Blog',
    'length'   => 55
  ), $atts);

  $category_id = get_cat_id($a['category']);
  $category_link = esc_url(get_category_link($category_id));

  $args = array(
    'numberposts' => 1,
    'category'    => $category_id,
    'post_type'   => 'post',
    'post_status' => 'publish',
    'order'       => 'DESC',
    'orderby'     => 'post_date',
  );

  $posts = wp_get_recent_posts( $args );
  $post_id = $posts[0]['ID'];
  $post = $posts[0];

  // $post = get_post($post_id);
  // $thumb = 'https://www.placecage.com/270/270';
  $thumb = random_image($a['category']);
  if ( has_post_thumbnail() ) {
  	$thumb = the_post_thumbnail(array(270, 270)); // @see https://developer.wordpress.org/reference/functions/the_post_thumbnail/
  }
  setup_postdata($post);

  $permalink  = get_permalink($post["ID"]);
  $esc_title  = esc_attr($post['post_title']);
  $post_day   = get_the_date('d', $post_id);
  $post_month = get_the_date('M Y', $post_id);
  $post_title = get_the_title($post['ID']);
  $excerpt_trim = strip_shortcodes($post['post_content']);
  $excerpt    = wp_trim_words($excerpt_trim, $a['length']) . ' <a href="'. get_permalink($post["ID"]) . '">READ MORE</a>';


  $output = <<<EOT
  <div class="col-md-6">
    <article id="post-{$post_id}" class="post post-{$post_id} type-post status-publish format-standard has-post-thumbnail hentry category-photo" itemtype="http://schema.org/BlogPosting">
      <div class="rowtight">
        <div class="imagehoverclass col-md-5 col-sm-12" itemprop="image" itemtype="https://schema.org/ImageObject">
          <a href="{$permalink}">
            <img alt="{$esc_title}" src="{$thumb}" />
            <meta itemprop="url" content="{$thumb}">
						<meta itemprop="width" content="270">
						<meta itemprop="height" content="270"> </a>
          </a>
        </div>
        <div class="col-md-7 col-sm-12 postcontent">
          <div class="postmeta updated color_gray">
            <div class="postdate bg-lightgray headerfont" itemprop="datePublished">
              <span class="postday">{$post_day}</span> {$post_month}
            </div>
          </div>
          <header class="home_blog_title">
						<a href="{$permalink}">
							<h4 class="entry-title" itemprop="name headline">{$post_title}</h4>
						</a>
						<div class="subhead color_gray">
							<span class="category"><i class="fa fa-folder-open"></i>
                <a href="{$category_link}" title="{$a['category']}">{$a['category']}</a>
              </span>
						</div>
					</header>
          <div class="entry-content">
            <p>{$excerpt}</p>
          </div>
          <footer>
            <meta itemscope="" itemprop="mainEntityOfPage" itemtype="https://schema.org/WebPage" itemid="{$permalink}">
                    <meta itemprop="dateModified" content="{$post['post_modified_gmt']}">
                    <div itemprop="publisher" itemscope="" itemtype="https://schema.org/Organization">
                        <div itemprop="logo" itemscope="" itemtype="https://schema.org/ImageObject">
                            <meta itemprop="url" content="https://www.diglib.org/wp-content/uploads/2013/07/DLFrev1BL_notag_200.png">
                            <meta itemprop="width" content="275">
                            <meta itemprop="height" content="84">
                        </div>
                        <meta itemprop="name" content="{$post_title}">
                    </div>
          </footer>
        </div>
      </div>
    </article>
  </div>
EOT;

  return $output;
}

function register_shortcodes()
{
  add_shortcode('clearboth', 'clir_clearfix');
  add_shortcode('community_calendar', 'community_calendar');
  add_shortcode('recent_publications', 'clir_reports_view');
  add_shortcode('email', 'hide_email');
  add_shortcode('dlf_post', 'dlf_post');
  add_shortcode('menu_entry', 'dlf_menu_entry');
}

add_action('init', 'register_shortcodes');

// Utility Functions TODO refactor to utilities.php
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

     if (mb_strlen($excerpt) > $charlength) {
         $subex = mb_substr($excerpt, 0, $charlength - 5);
         $exwords = explode(' ', $subex);
         $excut = - (mb_strlen($exwords[ count($exwords) - 1 ]));
         if ($excut < 0) {
             $output = mb_substr($subex, 0, $excut);
         } else {
             $output = $subex;
         }
         echo '[...]';
     }

     return $output;
 }

 // TODO: move to utilities
 function debug($content)
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
