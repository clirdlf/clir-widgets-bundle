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

function register_shortcodes()
{
  add_shortcode('clearboth', 'clir_clearfix');
  add_shortcode('community_calendar', 'community_calendar');
  add_shortcode('recent_publications', 'clir_reports_view');
  add_shortcode('email', 'hide_email');
}

add_action('init', 'register_shortcodes');
