<?php
/** Other filters, invalid contexts, translations and link escaping. */
clir_test_assert( 'Example [&hellip;]' === wpdocs_excerpt_more( 'Example [&hellip;]', null ), 'Invalid post context changed the excerpt.' );
clir_test_assert( 'Example [&hellip;]' === wpdocs_excerpt_more( 'Example [&hellip;]', new stdClass() ), 'Unexpected post object changed the excerpt.' );
$id = wp_insert_post( array( 'post_status' => 'publish', 'post_title' => 'Excerpt edge case', 'post_content' => str_repeat( 'Example word ', 100 ) ), true );
clir_test_assert( ! is_wp_error( $id ) && $id > 0, 'Cannot create excerpt fixture.' );
$suffix = static function () { return ' [custom suffix]'; };
$translation = static function ( $translated, $text, $domain ) {
    return 'Read More' === $text && 'clir-widgets-bundle' === $domain ? 'Lire <suite> & détails' : $translated;
};
$url = static function ( $permalink, $post ) use ( $id ) {
    return $post->ID === $id ? 'https://example.org/read?one=1&two="quoted"' : $permalink;
};
try {
    add_filter( 'excerpt_more', $suffix );
    $excerpt = get_the_excerpt( $id );
    clir_test_assert( str_ends_with( $excerpt, ' [custom suffix]' ) && ! str_contains( $excerpt, 'read-more' ), 'Another filter\'s excerpt suffix was replaced.' );
    remove_filter( 'excerpt_more', $suffix );
    add_filter( 'gettext', $translation, 10, 3 );
    add_filter( 'post_link', $url, 10, 2 );
    $excerpt = get_the_excerpt( $id );
    $parser = clir_test_element( $excerpt, 'A' );
    clir_test_assert( 'https://example.org/read?one=1&two=quoted' === $parser->get_attribute( 'href' ), 'Excerpt permalink escaping failed.' );
    clir_test_assert( str_contains( $excerpt, 'Lire &lt;suite&gt; &amp; détails' ) && ! str_contains( $excerpt, '<suite>' ), 'Translated excerpt label was not escaped using the plugin domain.' );
} finally {
    remove_filter( 'excerpt_more', $suffix );
    remove_filter( 'gettext', $translation, 10 );
    remove_filter( 'post_link', $url, 10 );
    wp_delete_post( $id, true );
}
echo "Excerpt suffix, context, translation and URL checks passed.\n";
