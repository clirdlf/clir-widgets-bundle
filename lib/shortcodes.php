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
 * Shortcode for embedding map in to a page
 *
 * @param array $attr Map shortcode attributes.
 * @return string Map container markup.
 */
function map( $attr ) {
	$a = shortcode_atts(
		array(
			'data'  => 'https://clirdlf.github.io/maps/data.js',
			'layer' => '',
		),
		$attr
	);

	$data = array(
		'layer' => $a['layer'],
	);
	// Use a local cache revision for remote resources without a pinned release.
	$asset_version = (string) filemtime( __FILE__ );
	$map_version   = (string) filemtime( CLIR_WIDGETS_PLUGIN_PATH . 'js/map.js' );
	wp_enqueue_style( 'leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.css', array(), '1.0.3' );
	wp_enqueue_style( 'MarkerCluster', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/MarkerCluster.css', array( 'leaflet' ), '1.0.3' );
	wp_enqueue_style( 'MarkerCluster-Default', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/MarkerCluster.Default.css', array( 'MarkerCluster' ), '1.0.3' );
	wp_enqueue_script( 'leaflet', 'https://unpkg.com/leaflet@1.0.3/dist/leaflet.js', array(), '1.0.3', true );
	wp_enqueue_script( 'map-data', 'https://clirdlf.github.io/maps/data.js', array(), $asset_version, true );
	wp_enqueue_script( 'markercluster', 'https://unpkg.com/leaflet.markercluster@1.0.3/dist/leaflet.markercluster.js', array( 'leaflet' ), '1.0.3', true );
	wp_enqueue_script( 'oms', 'http://jawj.github.io/OverlappingMarkerSpiderfier-Leaflet/bin/oms.min.js', array( 'leaflet' ), $asset_version, true ); // TODO: Don't hotlink this.
	wp_enqueue_script( 'map', plugins_url( '/js/map.js', __DIR__ ), array( 'jquery', 'leaflet', 'map-data', 'markercluster', 'oms' ), $map_version, true );
	wp_localize_script( 'map', 'php_vars', $data );
	$output = '<div id="clir_map" style="width:100%;height:600px;"></div>';
	return $output;
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
	add_shortcode( 'clir_map', 'map' );
	add_shortcode( 'image_frame', 'image_frame' );
}


add_action( 'init', 'register_shortcodes' );
