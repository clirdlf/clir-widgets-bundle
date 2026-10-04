<?php
/** Run with wp eval-file after installing and activating the plugin. */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'clir-widgets-bundle/clir-widgets-bundle.php' ) ) {
    throw new RuntimeException( 'Plugin was not activated.' );
}

if ( '<div class="clearfix visible-xs-block"></div>' !== do_shortcode( '[clearboth]' ) ) {
    throw new RuntimeException( 'Clearboth shortcode failed.' );
}

if ( false === strpos( do_shortcode( '[email]example@example.org[/email]' ), 'mailto:' ) ) {
    throw new RuntimeException( 'Email shortcode failed.' );
}

foreach ( array( 'clearboth', 'iframe', 'email', 'image_frame' ) as $tag ) {
    if ( ! shortcode_exists( $tag ) || ! is_callable( $GLOBALS['shortcode_tags'][ $tag ] ) ) {
        throw new RuntimeException( 'Active shortcode missing or invalid: ' . $tag );
    }
}

foreach ( array( 'icon', 'community_calendar', 'recent_publications', 'publication', 'random_publication', 'last_featured', 'program_spotlight', 'dlf_post', 'dlf_news', 'menu_entry', 'clir_modal_window', 'clir_map' ) as $tag ) {
    if ( shortcode_exists( $tag ) ) {
        throw new RuntimeException( 'Retired shortcode still registered: ' . $tag );
    }
}

// Excerpts for an explicit post must not link to the unrelated global post.
$original_post = $GLOBALS['post'] ?? null;
$excerpt_posts = array();
try {
    foreach ( array( 'CLIR global context', 'CLIR excerpt target' ) as $title ) {
        $post_id = wp_insert_post(
            array(
                'post_title'   => $title,
                'post_content' => str_repeat( 'Excerpt word ', 100 ),
                'post_status'  => 'publish',
            ),
            true
        );
        if ( is_wp_error( $post_id ) ) {
            throw new RuntimeException( $post_id->get_error_message() );
        }
        $excerpt_posts[] = $post_id;
    }
    $GLOBALS['post'] = get_post( $excerpt_posts[0] );
    $excerpt = get_the_excerpt( $excerpt_posts[1] );
    if ( false === strpos( $excerpt, 'href="' . esc_url( get_permalink( $excerpt_posts[1] ) ) . '"' ) ) {
        throw new RuntimeException( 'Excerpt link did not use the requested post.' );
    }
    if ( false !== strpos( $excerpt, 'href="' . esc_url( get_permalink( $excerpt_posts[0] ) ) . '"' ) ) {
        throw new RuntimeException( 'Excerpt link used the unrelated global post.' );
    }

    wp_update_post( array( 'ID' => $excerpt_posts[1], 'post_excerpt' => 'A manual excerpt [&hellip;]' ) );
    if ( 'A manual excerpt [&hellip;]' !== get_the_excerpt( $excerpt_posts[1] ) ) {
        throw new RuntimeException( 'Manual excerpt was changed.' );
    }
    wp_update_post( array( 'ID' => $excerpt_posts[1], 'post_excerpt' => '', 'post_content' => 'Short excerpt.' ) );
    if ( false !== strpos( get_the_excerpt( $excerpt_posts[1] ), 'read-more' ) ) {
        throw new RuntimeException( 'Short excerpt received an unnecessary link.' );
    }
} finally {
    $GLOBALS['post'] = $original_post;
    foreach ( $excerpt_posts as $post_id ) {
        wp_delete_post( $post_id, true );
    }
}

// Activation is not a full rendering test. Remaining rendering issues are in the audit.
echo "Plugin activation and basic shortcode smoke checks passed.\n";
