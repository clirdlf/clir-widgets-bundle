<?php

require_once 'utilities.php';

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
     ),
       $attr
   );

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
 function hide_email($atts, $content = null)
 {
     // guard for accidental wrap
     if (! is_email($content)) {
         return;
     }

     return '<a href="mailto:"' . antispambot($content) . '">' . antispambot($content) . '</a>';
 }

 function map($attr)
 {
     $a = shortcode_atts(
     array(
       'data'  => 'https://clirdlf.github.io/maps/data.js',
       'layer' => ''
     ),
       $attr
   );

     $data = array(
       'layer' => $a['layer']
     );

     wp_enqueue_style('leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.css');
     wp_enqueue_style('MarkerCluster', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/MarkerCluster.css');
     wp_enqueue_style('MarkerCluster-Default', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/MarkerCluster.Default.css');

     wp_enqueue_script('leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.js');
     wp_enqueue_script('map-data', 'https://clirdlf.github.io/maps/data.js');
     wp_enqueue_script('markercluster', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/leaflet.markercluster.js');
     wp_enqueue_script('oms', 'http://jawj.github.io/OverlappingMarkerSpiderfier-Leaflet/bin/oms.min.js'); // TODO: Don't hotlink this
     wp_enqueue_script('map', plugins_url('/js/map.js', dirname(__FILE__)), array('leaflet'));
     wp_localize_script('map', 'php_vars', $data);
     //https://cdn.rawgit.com/clirdlf/logo-fonts/master/clir-font/stylesheet.min.css
     $output = '<div id="clir_map" style="width:100%;height:600px;"></div>';
     return $output;
 }

/**
 * Display DLF menu items
 *
 * @param array $atts Shortcode attributes
 *
 * @return string HTML content
 */
 function dlf_menu_entry($attr)
 {
     $a = shortcode_atts(
     array(
       'link'  => '/',
       'icon'  => 'fa fa-ban',
       'title' => ''
     ),
       $attr
   );

     $output = <<<EOT
<div class="col-lg-3 col-md-3 col-sm-6 col-xs-6 home-iconmenu">
  <a href="{$a['link']}"> <i class="{$a['icon']}" aria-hidden="true"></i>
    <h4>{$a['title']}</h4>
  </a>
</div>
EOT;

     return $output;
 }

 function get_thumb($publication)
 {
     $output = '';
     $attachments = get_attached_media('application/pdf', $publication->ID);
     $pdf = reset($attachments);
     $thumb_id = get_post_thumbnail_id($pdf);

     if ($thumbnail_id = get_post_thumbnail_id($pdf->ID)) {
         $output .= '<a class="pdf-link image-link" href="' . get_page_link($publication->ID) . '" title="'.esc_attr(get_the_title($pdf)).'">'.wp_get_attachment_image($thumbnail_id, array(155,200), false, array("class" => "")).'</a>';
     }
     $output .= '<a href="' . get_page_link($publication->ID). '">';

     return $output;
 }

 function display_publication($publication)
 {
     $attachments = get_attached_media('application/pdf', $publication->ID);
     $thumb = get_thumb($publication);
     $page_link = get_page_link($publication->ID);

     $output = <<< EOT
<div class="col-sm-6 col-md-4">
   <div class="report-cover">
      <div class="img-frame rounded">
         <div class="img-box figcaption-middle text-center">
            {$thumb}
            <a href="{$page_link}">
               <h3>{$publication->post_title}</h3>
            </a>
         </div>
      </div>
   </div>
</div>
EOT;

     return $output;
 }

 function clir_random_publications($attr)
 {
     $a = shortcode_atts(
     array(
       'count'  => '1',
       'parent' => '240'
     ),
       $attr
   );

     $query = new WP_Query(
       array(
         'orderby' => 'rand',
         'posts_per_page' => $a['count'],
         'post_type' =>   'page',
         'post_parent' => $a['parent']
       )
     );

     reset($query);
     $output = display_publication( $query->posts[0] );

     return $output;
 }

 function clir_publications($attr)
 {
     $a = shortcode_atts(
        array(
          'title'  => '',
       ),
       $attr
    );

     $publication = get_page_by_title($a['title']);
     $output = display_publication( $publication );
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
function clir_recent_reports($attr)
{
    $a = shortcode_atts(
    array(
      'parent'      => 240,
      'count'       => '1',
      'sort_order'  => 'desc',
      'sort_column' => 'post_date',
    ),
      $attr
  );

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

    foreach ($pages as $publication) {
      $output .= display_publication( $publication );
    }

    return $output;

}

  /**
  * For embedding the DLF Community Calendar (managed in Google Calendar)
  *
  * @return String embed code for the community calendar. Can be overridden for
  * other Google calendars
  */
  function community_calendar($atts)
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

function category_lookup($categories)
{
    $category_ids = array();
    foreach ($categories as $category) {
        $category_id = "-" . get_cat_id($category);
        array_push($category_ids, $category_id);
    }
    return implode(",", $category_ids);
}

function dlf_excerpt($limit = 50, $source = null)
{
    if ($source == "content" ? ($excerpt = get_the_content()) : ($excerpt = get_the_excerpt()));
    $excerpt = preg_replace(" (\[.*?\])", '', $excerpt);
    $excerpt = strip_shortcodes($excerpt);
    $excerpt = strip_tags($excerpt);
    $excerpt = substr($excerpt, 0, $limit);
    $excerpt = substr($excerpt, 0, strripos($excerpt, " "));
    $excerpt = trim(preg_replace('/\s+/', ' ', $excerpt));
    $excerpt = $excerpt.'... <a href="' . get_permalink($post->ID) . '">more <i class="fa fa-angle-double-right" aria-hidden="true"></i></a>';
    return $excerpt;
}

function dlf_news($atts)
{
    $a = shortcode_atts(array(
    'exclude_categories' => '',
    'length'   => 55,
    'count'    => 3,
    'class'    => 'col-md-6'
  ), $atts);

    $categories = explode(',', $a['exclude_categories']);
    $category_ids = category_lookup($categories);

    $args = array(
     'cat' => $category_ids,
     'posts_per_page' => $a['count']
   );

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        // echo '<div class="col-md-12">';
        $output = '<div class="top-posts">';

        while ($query->have_posts()) {
            $query->the_post();
            $output .= '<h3><a href="' . get_post_permalink() . '">' . get_the_title() . '</a></h3>';
            $output .= '<div class="post-meta">';
            $output .= '<ul class="entry-meta">';
            $output .= '<li class="entry-date"><i class="fa fa-calendar" aria-hidden="true"></i> ' . get_the_date() .'</li>';
            $output .= '<li class="entry-date"><i class="fa fa-user" aria-hidden="true"></i> ' . get_the_author() .'</li>';
            $output .= '<li class="entry-date"><i class="fa fa-file-o" aria-hidden="true"></i> ' . get_the_category_list(', ') .'</li>';
            $output .= '</ul>';
            $output .= '</div>';
            $output .= '<p class="excerpt">' . dlf_excerpt(255) . '</p>';
        }

        $output .= '<div class="text-center"><a href="' . get_permalink(get_option('page_for_posts')) . '" class="btn btn-dlf">More Posts</a></div>';
        echo '</div>';
        /* Restore original Post Data */
        wp_reset_postdata();
    } else {
    }

    return $output;
}

function dlf_post($atts)
{
    $a = shortcode_atts(array(
    'category' => 'Blog',
    'length'   => 55,
    'class'    => 'col-md-6'
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

    $posts = wp_get_recent_posts($args);
    $post_id = $posts[0]['ID'];
    $post = $posts[0];

    // $post = get_post($post_id);
    // $thumb = 'https://www.placecage.com/270/270';
    $thumb = random_image($a['category']);
    if (has_post_thumbnail()) {
        $thumb = the_post_thumbnail(array(270, 270)); // @see https://developer.wordpress.org/reference/functions/the_post_thumbnail/
    }

    setup_postdata($post);

    $permalink    = get_permalink($post["ID"]);
    $esc_title    = esc_attr($post['post_title']);
    $post_day     = get_the_date('d', $post_id);
    $post_month   = get_the_date('M Y', $post_id);
    $post_title   = get_the_title($post['ID']);
    $excerpt_trim = strip_shortcodes($post['post_content']);
    // $excerpt    = wp_trim_words($excerpt_trim, $a['length']) . ' <a href="'. get_permalink($post["ID"]) . '">READ MORE</a>';
    $excerpt = '';

    $output = <<<EOT
  <div class="{$a['class']}">
    <a href="{$permalink}" title="{$post['post_title']}" class="related-post" style="background-image: url({$thumb});">
      <span class="related-post-title">{$post['post_title']}</span>
    </a>
  </div>
EOT;

    return $output;
}

function register_shortcodes()
{
    add_shortcode('clearboth', 'clir_clearfix');
    add_shortcode('community_calendar', 'community_calendar');
    add_shortcode('recent_publications', 'clir_recent_reports');
    add_shortcode('publication', 'clir_publications');
    add_shortcode('random_publication', 'clir_random_publications');
    add_shortcode('email', 'hide_email');
    add_shortcode('clir_map', 'map');
    add_shortcode('dlf_post', 'dlf_post');
    add_shortcode('dlf_news', 'dlf_news');
    add_shortcode('menu_entry', 'dlf_menu_entry');
}

add_action('init', 'register_shortcodes');
