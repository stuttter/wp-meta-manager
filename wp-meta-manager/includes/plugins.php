<?php

/**
 * Meta Manager Plugin Integrations
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register EDD's customer meta.
 *
 * @since 1.0
 */
function wp_meta_add_easy_digital_downloads_meta() {

	// Register customer meta
	wp_register_meta_type( 'customer', array( 'table_name' => 'edd_customermeta' ) );

}
add_action( 'wp_register_meta_types', 'wp_meta_add_easy_digital_downloads_meta' );

/**
 * Register AffiliateWP's affiliate meta.
 *
 * @since 1.0
 */
function wp_meta_add_affiliatewp_meta() {

	// Register affiliate meta
	wp_register_meta_type( 'affiliate', array( 'table_name' => 'affiliate_wp_affiliatemeta' ) );

}
add_action( 'wp_register_meta_types', 'wp_meta_add_affiliatewp_meta' );