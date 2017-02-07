<?php

/**
 * Meta Manager Admin
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Adds the Metadata menu under Tools
 *
 * @since 1.0
 */
function wp_meta_manager_admin_menu() {

	$hook = add_management_page( __( 'Meta Manager', 'wp-meta-manager' ), __( 'Meta Manager', 'wp-meta-manager' ), 'manage_options', 'wp-meta-manager', 'wp_meta_manager_admin' );

}

/**
 * Render Meta Manager admin
 *
 * @since 1.0
 */
function wp_meta_manager_admin() {
	echo 'test';
}