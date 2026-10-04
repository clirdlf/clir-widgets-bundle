<?php
/** Create a real upload and generated thumbnail, with cleanup on failure. */
function clir_test_media_fixture() {
    require_once ABSPATH . 'wp-admin/includes/image.php';
    $image = imagecreatetruecolor( 400, 300 );
    ob_start();
    imagepng( $image );
    $bytes = ob_get_clean();
    $upload = wp_upload_bits( 'clir-test-' . wp_generate_uuid4() . '.png', null, $bytes );
    clir_test_assert( ! $upload['error'], 'Cannot create test upload: ' . $upload['error'] );
    $id = 0;
    $settings = array();
    foreach ( array( 'thumbnail_size_w', 'thumbnail_size_h', 'thumbnail_crop' ) as $key ) {
        $settings[ $key ] = get_option( $key );
        update_option( $key, 'thumbnail_crop' === $key ? 1 : 150 );
    }
    try {
        $id = wp_insert_attachment( array( 'post_title' => 'CLIR integration image', 'post_mime_type' => 'image/png', 'post_status' => 'inherit' ), $upload['file'], 0, true );
        clir_test_assert( ! is_wp_error( $id ) && $id > 0, 'Cannot insert test attachment.' );
        $metadata = wp_generate_attachment_metadata( $id, $upload['file'] );
        wp_update_attachment_metadata( $id, $metadata );
        clir_test_assert( isset( $metadata['sizes']['thumbnail'] ), 'WordPress did not generate a real thumbnail.' );
        $thumbnail = wp_get_attachment_image_src( $id, 'thumbnail' );
        clir_test_assert( $thumbnail && $thumbnail[0] !== $upload['url'], 'Thumbnail API returned the original image.' );
        clir_test_assert( file_exists( dirname( $upload['file'] ) . '/' . $metadata['sizes']['thumbnail']['file'] ), 'Generated thumbnail file is missing.' );
        return array( 'id' => $id, 'url' => $upload['url'], 'file' => $upload['file'], 'metadata' => $metadata, 'thumbnail' => $thumbnail[0] );
    } catch ( Throwable $error ) {
        if ( is_int( $id ) && $id > 0 ) {
            wp_delete_attachment( $id, true );
        } elseif ( file_exists( $upload['file'] ) ) {
            unlink( $upload['file'] );
        }
        throw $error;
    } finally {
        foreach ( $settings as $key => $value ) {
            if ( false === $value ) {
                delete_option( $key );
            } else {
                update_option( $key, $value );
            }
        }
    }
}
function clir_test_delete_media( $fixture ) {
    // Restore metadata so WordPress can also delete every generated size.
    wp_update_attachment_metadata( $fixture['id'], $fixture['metadata'] );
    wp_delete_attachment( $fixture['id'], true );
    clir_test_assert( ! file_exists( $fixture['file'] ), 'Test upload was not cleaned up.' );
    foreach ( $fixture['metadata']['sizes'] as $size ) {
        clir_test_assert( ! file_exists( dirname( $fixture['file'] ) . '/' . $size['file'] ), 'Generated test image size was not cleaned up.' );
    }
}
