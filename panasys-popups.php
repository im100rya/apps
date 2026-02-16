<?php
/**
 * Plugin Name: Panasys Popups
 * Plugin URI: https://panasys.in/wordpress-plugins/
 * Description: Build accessible pop-ups for shortcodes, forms, images, and any editor content.
 * Version: 1.0.0
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Author: 100rya
 * Author URI: https://panasys.in/wordpress-plugins/
 * License: GPL-2.0-or-later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain: panasys-popups
 * Domain Path: /languages
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

define( 'PANASYS_POPUPS_VERSION', '1.0.0' );
define( 'PANASYS_POPUPS_FILE', __FILE__ );
define( 'PANASYS_POPUPS_DIR', plugin_dir_path( __FILE__ ) );
define( 'PANASYS_POPUPS_URL', plugin_dir_url( __FILE__ ) );

require_once PANASYS_POPUPS_DIR . 'includes/class-panasys-popups-plugin.php';

/**
 * Bootstrap plugin.
 *
 * @return Panasys_Popups_Plugin
 */
function panasys_popups() {
	return Panasys_Popups_Plugin::instance();
}

panasys_popups();
