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

	if( ! function_exists( 'EDD' ) ) {
		return;
	}

	// Register customer meta
	wp_register_meta_type( 'customer', array( 
		'table_name' => 'edd_customermeta',
		'object_url' => admin_url( 'edit.php?post_type=download&page=edd-customers&view=overview&id=%d' )
	) );

}
add_action( 'wp_register_meta_types', 'wp_meta_add_easy_digital_downloads_meta' );

/**
 * Register AffiliateWP's affiliate meta.
 *
 * @since 1.0
 */
function wp_meta_add_affiliatewp_meta() {

	if( ! function_exists( 'affiliate_wp' ) ) {
		return;
	}

	// Register affiliate meta
	wp_register_meta_type( 'affiliate', array( 'table_name' => 'affiliate_wp_affiliatemeta' ) );

}
add_action( 'wp_register_meta_types', 'wp_meta_add_affiliatewp_meta' );