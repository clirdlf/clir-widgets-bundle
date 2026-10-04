<?php
/**
 * Read-only shortcode inventory: wp eval-file scripts/inventory-shortcodes.php [all]
 * Prints CSV to stdout and per-tag document counts to stderr. Never renders content.
 */
if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
	fwrite( STDERR, "Run this file with wp eval-file inside a WordPress installation.\n" );
	exit( 1 );
}

global $wpdb;

// Keep this list in sync with lib/shortcodes.php, including missing callbacks.
$tags = array(
	'clearboth', 'icon', 'iframe', 'community_calendar', 'recent_publications',
	'publication', 'random_publication', 'last_featured', 'program_spotlight',
	'email', 'clir_map', 'dlf_post', 'dlf_news', 'menu_entry', 'image_frame',
	'clir_modal_window',
);
$mode = $args[0] ?? 'publish';
if ( ! in_array( $mode, array( 'publish', 'all' ), true ) || count( $args ?? array() ) > 1 ) {
	WP_CLI::error( 'Usage: wp eval-file scripts/inventory-shortcodes.php [all]' );
}

// Detect exact opening tag names, including nested tags, without executing callbacks.
// Doubled opening brackets ([[tag]]) are escaped shortcode examples and are excluded.
$pattern = '/(?<!\[)\[(' . implode( '|', $tags ) . ')(?=[\s\/\]])/';
$totals  = array_fill_keys( $tags, 0 );
$last_id = 0;
$scanned = 0;
$output  = fopen( 'php://stdout', 'w' );
if ( false === $output ) {
	WP_CLI::error( 'Cannot open inventory output.' );
}
fputcsv( $output, array( 'site', 'shortcode', 'post_id', 'post_type', 'status', 'title', 'occurrences', 'url', 'edit_url' ), ',', '"', '' );

do {
	$status_filter = 'publish' === $mode ? "AND post_status = 'publish'" : '';
	$rows = $wpdb->get_results(
		$wpdb->prepare(
			"SELECT ID, post_type, post_status, post_title, post_content FROM {$wpdb->posts} WHERE ID > %d {$status_filter} ORDER BY ID ASC LIMIT 500",
			$last_id
		)
	);
	if ( $wpdb->last_error ) {
		WP_CLI::error( 'Inventory query failed: ' . $wpdb->last_error );
	}
	foreach ( $rows as $post ) {
		$last_id = (int) $post->ID;
		++$scanned;
		preg_match_all( $pattern, $post->post_content, $matches );
		foreach ( array_count_values( $matches[1] ) as $tag => $count ) {
			++$totals[ $tag ];
			fputcsv(
				$output,
				array(
					home_url(), $tag, $post->ID, $post->post_type, $post->post_status,
					$post->post_title, $count, get_permalink( $post->ID ) ?: home_url( '/?p=' . $post->ID ),
					admin_url( 'post.php?post=' . $post->ID . '&action=edit' ),
				),
				',', '"', ''
			);
		}
	}
} while ( count( $rows ) === 500 );
fclose( $output );

fwrite( STDERR, sprintf( "Scanned %d %s documents on %s. Documents per tag:\n", $scanned, 'publish' === $mode ? 'published' : 'total', home_url() ) );
foreach ( $totals as $tag => $count ) {
	fwrite( STDERR, sprintf( "%s: %d\n", $tag, $count ) );
}
