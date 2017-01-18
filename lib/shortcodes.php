<?php

function clir_posts_shortcode( $attr )
{

}

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
  return $iframe;
}

/**
 * Used in DLF theme...from old Construct theme (which is why it's gross)
 *
 * @return String
 */
/** function image_frame( $atts = null, $content = null ) {
  if( $atts == 'generator' ) {
    $option = array(
      'name' => __( 'Image Frames', 'clir' ),
      'value' => 'image_frame',
      'options' => array(
        array(
          'name' => __( 'Type', 'clir' ),
          'desc' => __( 'Choose which type of frame you wish to use.', 'clir' ),
          'id' => 'style',
          'default' => '',
          'options' => array(
            'border' => __( 'Transparent Border', 'clir' ),
            'reflect' => __( 'Reflection', 'clir' ),
            'framed' => __( 'Framed', 'clir' ),
            'shadow' => __( 'Shadow', 'clir' ),
            'reflect_shadow' => __( 'Reflection + Shadow', 'clir' ),
            'framed_shadow' => __( 'Framed + Shadow', 'clir' )
          ),
          'type' => 'select'
        ),
        array(
          'name' => __( 'Image URL', 'clir' ),
          'desc' => __( 'You can upload your image that you wish to use here.', 'clir' ),
          'id' => 'content',
          'default' => '',
          'type' => 'upload',
        ),
        array(
          'name' => __( 'Align <small>(optional)</small>', 'clir' ),
          'desc' => __( 'Set the alignment for your image here.<br /><br />Your image will float along the center, left or right hand sides depending on your choice.', 'clir' ),
          'id' => 'align',
          'default' => '',
          'options' => array(
            'left' => __( 'left', 'clir' ),
            'right' => __( 'right', 'clir' ),
            'center' => __( 'center', 'clir' )
          ),
          'type' => 'select'
        ),
        array(
          'name' => __( 'Alt Attribute <small>(optional)</small>', 'clir' ),
          'desc' => __( 'Type the alt text that you would like to display with your image here.', 'clir' ),
          'id' => 'alt',
          'default' => '',
          'type' => 'text'
        ),
        array(
          'name' => __( 'Title Attribute <small>(optional)</small>', 'clir' ),
          'desc' => __( 'Type the title text that you would like to display with your image here.', 'clir' ),
          'id' => 'title',
          'default' => '',
          'type' => 'text'
        ),
        array(
          'name' => __( 'Image Height <small>(optional)</small>', 'clir' ),
          'desc' => __( 'You can set the image height here.  Leave this blank if you do not want to resize your image.', 'clir' ),
          'id' => 'height',
          'default' => '',
          'type' => 'text'
        ),
        array(
          'name' => __( 'Image Width <small>(optional)</small>', 'clir' ),
          'desc' => __( 'You can set the image width here.  Leave this blank if you do not want to resize your image.', 'clir' ),
          'id' => 'width',
          'default' => '',
          'type' => 'text'
        ),
        'shortcode_has_atts' => true
      )
    );

    return $option;
  }

  extract(shortcode_atts(array(
    'style'		  => '',
    'align'		  => '',
    'alt'		  => '',
    'title'		  => '',
    'height'	  => '',
    'width'		  => '',
    'link_to' 	  => 'true',
    'prettyphoto' => 'true'
  ), $atts));

  global $wp_query, $mysite;

  $out = '';

  $effect = trim( $style );
  $effect = ( !empty( $effect ) ) ? $effect : 'framed';
  $align = ( $align == 'left' ? ' alignleft' : ( $align == 'right' ? ' alignright' : ( $align == 'center' ? ' aligncenter' : ' alignleft' ) ) );
  $class = ( $effect == 'reflect' ? "reflect{$align}" : ( $effect == 'reflect_shadow' ? 'reflect' : ( $effect == 'framed' ? "framed{$align}" : ( $effect == 'framed_shadow' ? 'framed' : '' ) ) ) );

  $width = ( !empty( $width ) ) ? trim(str_replace(' ', '', str_replace('px', '', $width ) ) ) : '';
  $height = ( !empty( $height ) ) ? trim(str_replace(' ', '', str_replace('px', '', $height ) ) ) : '';

  if( preg_match( '!https?://.+\.(?:jpe?g|png|gif)!Ui', $content, $matches ) ) {

    $out .= mysite_display_image(array(
      'src' 			=> $matches[0],
      'alt' 			=> $alt,
      'title' 		=> $title,
      'class' 		=> $class,
      'height' 		=> $height,
      'width'			=> $width,
      'link_to' 		=> ( $link_to == 'true' ? $matches[0] : false ),
      'prettyphoto'	=> ( $prettyphoto == 'true' ? true : false ),
      'align'			=> $align,
      'effect' 		=> $effect,
      //'wp_resize' 	=> ( mysite_get_setting( 'image_resize_type' ) == 'wordpress' ? true : false )
    ));
  }

  return $out;
}
*/

function register_shortcodes()
{
  add_shortcode('clearboth', 'clir_clearfix');
  add_shortcode('community_calendar', 'community_calendar');
  // add_shortcode('image_frame', 'clir_clearfix');
}

add_action('init', 'register_shortcodes');
