<?php
/**
 * Plugin Name: WP Meta Manager
 * Plugin URI:  https://wordpress.org/plugins/wp-meta-manager
 * Author:      John James Jacoby and Pippin Williamson
 * Author URI:  https://profiles.wordpress.org/johnjamesjacoby/
 * License:     GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 * Description: A robust management interface for WordPress meta data
 * Version:     1.0
 * Text Domain: wp-meta-manager
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Include the Meta Manager files
 *
 * @since 1.0
 */
function _wp_meta_manager() {

	// Get the plugin path
	$plugin_path = plugin_dir_path( __FILE__ ) . 'wp-meta-manager/';

	// Required Files
	require_once $plugin_path . 'includes/classes/class-wp-meta.php';
	require_once $plugin_path . 'includes/classes/class-wp-meta-type.php';
	require_once $plugin_path . 'includes/classes/class-wp-meta-list-table.php';
	require_once $plugin_path . 'includes/classes/class-wp-meta-data-query.php';
	require_once $plugin_path . 'includes/admin.php';
	require_once $plugin_path . 'includes/add.php';
	require_once $plugin_path . 'includes/cache.php';
	require_once $plugin_path . 'includes/capabilities.php';
	require_once $plugin_path . 'includes/functions.php';
	require_once $plugin_path . 'includes/hooks.php';
	require_once $plugin_path . 'includes/plugins.php';

	// Load translations
	load_plugin_textdomain( 'wp-meta-manager' );

}
add_action( 'plugins_loaded', '_wp_meta_manager' );

/**
 * Return the plugin URL
 *
 * @since 1.0
 *
 * @return string
 */
function wp_meta_manager_get_plugin_url() {
	return plugin_dir_url( __FILE__ ) . 'wp-meta-manager/';
}

/**
 * Return the asset version
 *
 * @since 1.0
 *
 * @return string
 */
function wp_meta_manager_get_asset_version() {
	return 201702070001;
}
