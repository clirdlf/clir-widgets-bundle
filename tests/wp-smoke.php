<?php
/** Run with wp eval-file after installing and activating the plugin. */
require_once ABSPATH . 'wp-admin/includes/plugin.php';
if ( ! is_plugin_active( 'clir-widgets-bundle/clir-widgets-bundle.php' ) ) {
    throw new RuntimeException( 'Plugin was not activated.' );
}

foreach ( array( 'Social_Media_Links' ) as $widget_class ) {
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

foreach ( array( 'clearboth', 'iframe', 'email', 'clir_map', 'image_frame' ) as $tag ) {
    if ( ! shortcode_exists( $tag ) || ! is_callable( $GLOBALS['shortcode_tags'][ $tag ] ) ) {
        throw new RuntimeException( 'Active shortcode missing or invalid: ' . $tag );
    }
}

foreach ( array( 'icon', 'community_calendar', 'recent_publications', 'publication', 'random_publication', 'last_featured', 'program_spotlight', 'dlf_post', 'dlf_news', 'menu_entry', 'clir_modal_window' ) as $tag ) {
    if ( shortcode_exists( $tag ) ) {
        throw new RuntimeException( 'Retired shortcode still registered: ' . $tag );
    }
}

// Activation is not a full rendering test. Remaining rendering issues are in the audit.
echo "Plugin activation, widget registration and basic shortcode smoke checks passed.\n";
