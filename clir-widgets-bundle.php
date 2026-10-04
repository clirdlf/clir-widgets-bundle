<?php
/**
 * Plugin Name: CLIR Widgets Bundle
 * Plugin URI: https://github.com/clirdlf/clir-widgets-bundle
 * Description: Custom WordPress widgets for CLIR + DLF websites
 * Text Domain: clir-widgets-bundle
 * Domain Path: /languages
 * Author: Council on Libraries and Information Resources
 * Version: 2.0.0
 * Author URI: https://www.clir.org
 * License: GPL3
 * License URI: https://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package CLIR_Widgets_Bundle
 */

// Stop direct requests before calling WordPress functions.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'CLIR_WIDGETS_PLUGIN_PATH', plugin_dir_path( __FILE__ ) );
define( 'CLIR_WIDGETS_PLUGIN_URL', plugin_dir_url( __FILE__ ) );

require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/filters.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/shortcodes.php';
require_once CLIR_WIDGETS_PLUGIN_PATH . 'lib/overrides.php';
