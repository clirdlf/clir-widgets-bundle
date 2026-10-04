<?php
/**
 * Classic editor configuration overrides.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Allow iframe elements in the classic visual editor.
 *
 * @param array $init_array TinyMCE initialization settings.
 * @return array Updated initialization settings.
 */
function add_iframe( $init_array ) {
	$existing = $init_array['extended_valid_elements'] ?? '';
	$elements = is_string( $existing ) ? explode( ',', $existing ) : array();
	foreach ( $elements as $element ) {
		if ( preg_match( '/^[+-]?iframe(?:\[|$)/i', trim( $element ) ) ) {
			return $init_array;
		}
	}
	$elements[]                            = 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]';
	$init_array['extended_valid_elements'] = implode( ',', array_filter( $elements ) );
	return $init_array;
}
// Preserve iframe elements in the visual editor.
add_filter( 'tiny_mce_before_init', 'add_iframe' );
