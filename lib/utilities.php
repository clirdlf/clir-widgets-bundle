<?php
/**
 * Legacy content and image utility functions.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Return a post excerpt using the legacy helper signature.
 *
 * @see https://codex.wordpress.org/Function_Reference/get_the_excerpt
 *
 * @param array $post       Post data containing an ID.
 * @param int   $charlength Requested length (currently not enforced).
 *
 * @return string The full post excerpt.
 */
function the_excerpt_max_charlength( $post, $charlength ) {
	$excerpts = get_the_excerpt( get_post( $post['ID'] ) );
	$excerpt  = $excerpts[0];

	$output = '';
	++$charlength;

	return $excerpts;
}

/**
 * Build legacy thumbnail link markup for a publication's PDF.
 *
 * @param WP_Post $publication Publication post.
 * @return string Thumbnail link markup and an opening publication link.
 */
function get_thumb( $publication ) {
	$output       = '';
	$attachments  = get_attached_media( 'application/pdf', $publication->ID );
	$pdf          = end( $attachments );
	$thumb_id     = get_post_thumbnail_id( $pdf );
	$thumbnail_id = get_post_thumbnail_id( $pdf->ID );
	if ( $thumbnail_id ) {
		$output .= '<a class="pdf-link image-link" href="' . get_page_link( $publication->ID ) . '" title="' . esc_attr( get_the_title( $pdf ) ) . '">' . wp_get_attachment_image( $thumbnail_id, 'medium', false, array( 'class' => 'img-responsive center-block' ) ) . '</a>';
	}
	$output .= '<a href="' . get_page_link( $publication->ID ) . '">';
	return $output;
}

/**
 * Return an escaped JSON representation for diagnostic display.
 *
 * @param mixed $content Value to inspect.
 * @return string Preformatted wrapper markup.
 */
function local_debug( $content ) {
	return '<pre>' . esc_html( wp_json_encode( $content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) ) . '</pre>';
}

/**
 * Normalize the first word of a category for image filename matching.
 *
 * @param string $category Category name.
 * @return string Normalized category prefix.
 */
function clean_category( $category ) {
	$cat = explode( ' ', $category );
	$str = $cat[0];
	$str = strtolower( $str );
	return preg_replace( '/[^A-Za-z0-9\-]/', '', $str );
}

/**
 * Select a random bundled image matching a category prefix.
 *
 * @param string $category Category name.
 * @return string Image URL.
 */
function random_image( $category ) {
	$cc     = clean_category( $category );
	$path   = CLIR_WIDGETS_PLUGIN_PATH . 'lib/images/dlf/' . $cc . '*.{jpg,jpeg,png,gif}';
	$images = glob( $path, GLOB_BRACE );
	$image  = $images[ array_rand( $images ) ];
	return plugin_dir_url( __FILE__ ) . 'images/dlf/' . basename( $image );
}
