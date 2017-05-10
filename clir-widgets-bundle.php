<?php
/**
 * Plugin Name: CLIR Widgets Bundle
 * Plugin URI: https://github.com/clirdlf/clir-widgets-bundle
 * Description: Custom WordPress widgets for CLIR + DLF websites
 * Text Domain: clir-widgets-bundle
 * Domain Path: /languages
 * Author: Council on Libraries and Information Resources
 * Version: 0.0.2
 * Author URI: https://www.clir.org
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 */

define('CLIR_WIDGETS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CLIR_WIDGETS_PLUGIN_URL', pluginS_URL(__FILE__));

// require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/utilities.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/calendar-widget.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/social-media-links.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/filters.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/shortcodes.php';

// Block direct requests
if (!defined('ABSPATH')) {
    die(-1);
}

add_action('widgets_init', 'clir_load_widgets');

/**
 * Load widgets
 * @return null
 */
function clir_load_widgets()
{
    register_widget('Community_Calendar_Widget');
    register_widget('Social_Media_Links');
}
