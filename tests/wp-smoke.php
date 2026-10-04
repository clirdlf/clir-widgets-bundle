<?php
/** Run with wp eval-file after installing and activating the plugin. */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'clir-widgets-bundle/clir-widgets-bundle.php' ) ) {
    throw new RuntimeException( 'Plugin was not activated.' );
}

foreach ( array( 'Social_Media_Links', 'Informz_Tracking_Widget' ) as $widget_class ) {
    if ( ! isset( $GLOBALS['wp_widget_factory']->widgets[ $widget_class ] ) ) {
        throw new RuntimeException( 'Widget not registered: ' . $widget_class );
    }
}

if ( '<div class="clearfix visible-xs-block"></div>' !== do_shortcode( '[clearboth]' ) ) {
    throw new RuntimeException( 'Clearboth shortcode failed.' );
}

if ( false === strpos( do_shortcode( '[email]example@example.org[/email]' ), 'mailto:' ) ) {
    throw new RuntimeException( 'Email shortcode failed.' );
}

// Activation is not a full rendering test. Known broken shortcodes are in the audit.
echo "Plugin activation, widget registration and basic shortcode smoke checks passed.\n";
