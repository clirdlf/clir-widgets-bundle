<?php
/** Verify attachment lookup and fallback against real records and files. */
require_once __DIR__ . '/media-fixture.php';
$fixture = clir_test_media_fixture();
try {
    clir_test_assert( $fixture['id'] === attachment_url_to_postid( $fixture['url'] ), 'Real upload URL did not resolve to its attachment.' );
    $html = do_shortcode( '[image_frame width="180" caption="Real attachment"]' . $fixture['url'] . '?version=2#preview[/image_frame]' );
    clir_test_assert( $fixture['thumbnail'] === clir_test_element( $html, 'IMG' )->get_attribute( 'src' ), 'Shortcode did not use the generated thumbnail.' );
    delete_post_meta( $fixture['id'], '_wp_attachment_metadata' );
    $html = do_shortcode( '[image_frame]' . $fixture['url'] . '[/image_frame]' );
    clir_test_assert( $fixture['url'] === clir_test_element( $html, 'IMG' )->get_attribute( 'src' ), 'Missing thumbnail metadata did not fall back to the original image.' );
} finally {
    clir_test_delete_media( $fixture );
}
echo "Real upload, thumbnail and missing-metadata checks passed.\n";
