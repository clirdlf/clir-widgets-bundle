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
	$init_array['extended_valid_elements'] = 'iframe[id|class|title|style|align|frameborder|height|longdesc|marginheight|marginwidth|name|scrolling|src|width]';
	return $init_array;
}
// Preserve iframe elements in the visual editor.
add_filter( 'tiny_mce_before_init', 'add_iframe' );
