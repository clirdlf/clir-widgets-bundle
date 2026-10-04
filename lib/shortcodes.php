<?php
/**
 * Shortcode callbacks and registration.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Normalize a shortcode value without converting arrays or objects to strings.
 *
 * @param mixed $value Attribute or content value.
 * @return string Scalar text, or an empty string.
 */
function clir_shortcode_text( $value ) {
	return is_scalar( $value ) ? (string) $value : '';
}

/**
 * Validate a positive pixel dimension or an optional percentage.
 *
 * @param mixed  $value         Supplied dimension.
 * @param string $fallback      Value used when invalid.
 * @param bool   $allow_percent Whether percentages are supported.
 * @return string Valid dimension or the fallback.
 */
function clir_shortcode_dimension( $value, $fallback = '', $allow_percent = false ) {
	$value = trim( clir_shortcode_text( $value ) );
	if ( preg_match( '/^[0-9]{1,5}$/', $value ) && (int) $value > 0 && (int) $value <= 10000 ) {
		return (string) (int) $value;
	}
	if ( $allow_percent && preg_match( '/^([0-9]{1,3})%$/', $value, $matches ) && (int) $matches[1] > 0 && (int) $matches[1] <= 100 ) {
		return (int) $matches[1] . '%';
	}
	return $fallback;
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

	$src = esc_url( trim( clir_shortcode_text( $a['src'] ) ), array( 'http', 'https' ) );
	if ( '' === $src ) {
		return '';
	}
	$width  = clir_shortcode_dimension( $a['width'], '800', true );
	$height = clir_shortcode_dimension( $a['height'], '600', true );

	return '<iframe src="' . $src . '" title="' . esc_attr( clir_shortcode_text( $a['title'] ) ) . '" width="' . esc_attr( $width ) . '" height="' . esc_attr( $height ) . '" allow="' . esc_attr( sanitize_text_field( clir_shortcode_text( $a['allow'] ) ) ) . '"></iframe>';
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

	$src  = esc_url_raw( trim( clir_shortcode_text( $content ) ), array( 'http', 'https' ) );
	$path = wp_parse_url( $src, PHP_URL_PATH );
	if ( '' === $src || ! is_string( $path ) || ! preg_match( '/\.(?:jpe?g|png|gif|webp|avif)$/i', $path ) ) {
		return '';
	}

	// Resolve real media sizes; keep the original URL when no attachment is found.
	$attachment_id = attachment_url_to_postid( preg_replace( '/[?#].*$/', '', $src ) );
	$thumbnail     = $attachment_id ? wp_get_attachment_image_src( $attachment_id, 'thumbnail' ) : false;
	if ( $thumbnail ) {
		$src = $thumbnail[0];
	}
	$width   = clir_shortcode_dimension( $a['width'] );
	$height  = clir_shortcode_dimension( $a['height'] );
	$classes = array_map( 'sanitize_html_class', preg_split( '/\s+/', trim( clir_shortcode_text( $a['style'] ) ) ) );
	$style   = '' !== $width ? ' style="max-width:' . esc_attr( $width ) . 'px"' : '';

	$image  = '<figure' . $style . ' class="wp-caption alignleft">';
	$image .= '<img class="' . esc_attr( implode( ' ', $classes ) ) . '" src="' . esc_url( $src, array( 'http', 'https' ) ) . '" title="' . esc_attr( clir_shortcode_text( $a['title'] ) ) . '" alt="' . esc_attr( clir_shortcode_text( $a['alt'] ) ) . '"';
	if ( '' !== $width ) {
		$image .= ' width="' . esc_attr( $width ) . '"';
	}
	if ( '' !== $height ) {
		$image .= ' height="' . esc_attr( $height ) . '"';
	}
	$image .= ' />';
	$image .= '<figcaption class="wp-caption-text">' . esc_html( clir_shortcode_text( $a['caption'] ) ) . '</figcaption>';
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
	$content = trim( clir_shortcode_text( $content ) );
	if ( ! is_email( $content ) ) {
		return '';
	}
	return '<a href="' . esc_attr( 'mailto:' . antispambot( $content ) ) . '">' . esc_html( antispambot( $content ) ) . '</a>';
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
