<?php
/**
 * Shortcode callbacks and registration.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Used in DLF theme; couldn't find what plugin contained this so I made one.
 * Updated for Bootstrap
 *
 * @see http://getbootstrap.com/css/#grid-example-mixed-complete
 *
 * @return String div with clearfix CSS added (including clearing XS cols if the
 * height doesnt match)
 */
function clir_clearfix() {
	return '<div class="clearfix visible-xs-block"></div>';
}

/**
 * Adds an iframe shortcode so visual editor doesn't strip the tags
 *
 * @see https://gist.github.com/codescribblr/8984457, changed for HTML5 compliance
 *
 * @param array $atts Attributes for the iframe.
 *
 * @return String iframe code
 */
function iframe( $atts ) {
	$a = shortcode_atts(
		array(
			'src'    => '',
			'title'  => '',
			'width'  => '800',
			'height' => '600',
			'allow'  => 'fullscreen',
			'style'  => 'border: 0px;',
		),
		$atts
	);

	$iframe = '<iframe src="' . $a['src'] . '"  title="' . $a['title'] . '" width="' . $a['width'] . '" height="' . $a['height'] . '" allow="' . $a['allow'] . '"></iframe>';

	return $iframe;
}

/**
 * Used in DLF theme; couldn't find what plugin contained this so I made one.
 * Updated for Bootstrap
 *
 * @see http://getbootstrap.com/css/#grid-example-mixed-complete
 *
 * @param array       $attr    Image attributes.
 * @param string|null $content Image URL enclosed by the shortcode.
 *
 * @return String An image that mimics the older [image_frame] shortcode
 */
function image_frame( $attr, $content = null ) {
	$a = shortcode_atts(
		array(
			'style'   => '',
			'alt'     => '',
			'height'  => '',
			'width'   => '',
			'title'   => '',
			'caption' => '',
		),
		$attr
	);

	// Reset image call.
	$pattern = '/^(.*).(jpg|png|jpeg)$/';
	preg_match( $pattern, $content, $matches );
	$thumb = $matches[1] . '-150x150.' . $matches[2];

	$image  = '<figure style="max-width:' . $a['width'] . 'px" class="wp-caption alignleft">';
	$image .= '<img class="' . $a['style'] . '" src="' . $thumb . '" title="' . $a['title'] . '" alt="' . $a['alt'] . '" width="' . $a['width'] . '" height="' . $a['height'] . '" />';
	$image .= '<figcaption class="wp-caption-text">' . $a['caption'] . '</figcaption>';
	$image .= '</figure>';
	return $image;
}

/**
 * Display deadline date
 *
 * @param array $attr Shortcode attributes.
 *
 * @return string HTML decorated date
 */
function deadline( $attr ) {
	$a      = shortcode_atts(
		array(
			'date'          => '',
			'program'       => '',
			'message'       => '',
			'after_message' => 'This year\'s deadline has passed.',
		),
		$attr
	);
	$output = '';
	return $output;
}
/**
 * Hide email from Spam Bots using a Shortcode
 *
 * @param array       $atts    Shortcode attributes (not used).
 * @param string|null $content The shortcode content (should be an email address).
 *
 * @return string An obfuscated email address
 */
function hide_email( $atts, $content = null ) {
	// Guard for accidental wrap.
	if ( ! is_email( $content ) ) {
		return;
	}
	return '<a href="mailto:' . antispambot( $content ) . '">' . antispambot( $content ) . '</a>';
}

/**
 * Register the retained shortcode callbacks.
 *
 * @return void
 */
function register_shortcodes() {
	add_shortcode( 'clearboth', 'clir_clearfix' );
	add_shortcode( 'iframe', 'iframe' );
	add_shortcode( 'email', 'hide_email' );
	add_shortcode( 'image_frame', 'image_frame' );
}


add_action( 'init', 'register_shortcodes' );
