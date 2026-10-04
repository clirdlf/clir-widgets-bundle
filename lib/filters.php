<?php
/**
 * Excerpt filters.
 *
 * @package CLIR_Widgets_Bundle
 */

/**
 * Replace the default automatic-excerpt suffix with a link to its post.
 *
 * @see https://developer.wordpress.org/reference/hooks/get_the_excerpt/
 *
 * @param string  $excerpt Filtered excerpt text.
 * @param WP_Post $post    The post whose excerpt is being generated.
 * @return string Excerpt with a read-more link when automatically truncated.
 */
function wpdocs_excerpt_more( $excerpt, $post ) {
	$more = ' [&hellip;]';
	if ( ! $post instanceof WP_Post || '' !== trim( $post->post_excerpt ) || ! str_ends_with( $excerpt, $more ) ) {
		return $excerpt;
	}

	$url = get_permalink( $post->ID );
	if ( ! $url ) {
		return $excerpt;
	}

	return substr( $excerpt, 0, -strlen( $more ) ) . sprintf(
		' <a class="read-more" href="%1$s">%2$s</a>',
		esc_url( $url ),
		esc_html__( 'Read More', 'clir-widgets-bundle' )
	);
}
// Run after WordPress generates the excerpt, using the supplied post context.
add_filter( 'get_the_excerpt', 'wpdocs_excerpt_more', 11, 2 );
