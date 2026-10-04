<?php
/** Run only in a disposable network; creates and deletes two subsites. */
require_once __DIR__ . '/bootstrap.php';
require_once ABSPATH . 'wp-admin/includes/plugin.php';
require_once ABSPATH . 'wp-admin/includes/ms.php';
require_once __DIR__ . '/media-fixture.php';
clir_test_assert( is_multisite(), 'Multisite tests require a WordPress network.' );
clir_test_assert( is_plugin_active_for_network( 'clir-widgets-bundle/clir-widgets-bundle.php' ), 'Plugin must be network activated.' );
$origin = get_current_blog_id();
$origin_url = home_url();
$origin_uploads = wp_upload_dir();
$network = get_network();
$admins = get_super_admins();
$admin = get_user_by( 'login', $admins[0] );
$sites = array();
$fixtures = array();
try {
    foreach ( array( 'one', 'two' ) as $name ) {
        $path = trailingslashit( $network->path ) . 'clir-test-' . $name . '-' . wp_generate_uuid4() . '/';
        $id = wpmu_create_blog( $network->domain, $path, 'CLIR test ' . $name, $admin->ID, array( 'public' => 1 ), $network->id );
        clir_test_assert( ! is_wp_error( $id ) && $id > 0, 'Cannot create test subsite.' );
        $sites[] = $id;
        switch_to_blog( $id );
        try {
            clir_test_assert( home_url() !== $origin_url, 'Subsite URL remained on the originating site.' );
            ( static function () {
                require __DIR__ . '/wp-smoke.php';
            } )();
            $fixtures[ $id ] = clir_test_media_fixture();
        } finally {
            restore_current_blog();
        }
    }
    clir_test_assert( $fixtures[ $sites[0] ]['url'] !== $fixtures[ $sites[1] ]['url'], 'Subsite uploads are not isolated.' );
    foreach ( $sites as $index => $id ) {
        switch_to_blog( $id );
        try {
            $own = $fixtures[ $id ];
            $other = $fixtures[ $sites[ 1 - $index ] ];
            $html = do_shortcode( '[image_frame]' . $own['url'] . '[/image_frame]' );
            clir_test_assert( $own['thumbnail'] === clir_test_element( $html, 'IMG' )->get_attribute( 'src' ), 'Subsite resolved the wrong attachment thumbnail.' );
            $html = do_shortcode( '[image_frame]' . $other['url'] . '[/image_frame]' );
            clir_test_assert( $other['url'] === clir_test_element( $html, 'IMG' )->get_attribute( 'src' ), 'Cross-site URL resolved to a local attachment.' );
        } finally {
            restore_current_blog();
        }
        clir_test_assert( $origin === get_current_blog_id() && $origin_url === home_url() && $origin_uploads['baseurl'] === wp_upload_dir()['baseurl'], 'Site or upload context leaked after restore_current_blog().' );
    }
} finally {
    foreach ( $sites as $id ) {
        switch_to_blog( $id );
        try {
            if ( isset( $fixtures[ $id ] ) ) {
                clir_test_delete_media( $fixtures[ $id ] );
            }
        } finally {
            restore_current_blog();
        }
        wpmu_delete_blog( $id, true );
    }
}
echo "Network activation, two-subsite rendering and context isolation checks passed.\n";
