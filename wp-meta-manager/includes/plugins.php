<?php

/**
 * Meta Manager Plugin Integrations
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register Easy Digital Download's meta.
 *
 * @since 1.0.0 Support EDD 2.x
 * @since 2.0.0 Support EDD 3.0
 */
function wp_meta_add_easy_digital_downloads_meta() {

	// Bail if no EDD
	if ( ! function_exists( 'EDD' ) ) {
		return;
	}

	// Register customer meta
	if ( function_exists( 'edd_add_customer_meta' ) ) {
		wp_register_meta_type( 'customer', array(
			'table_name' => 'edd_customermeta',
			'columns'    => array(
				'object_id' => 'edd_customer_id'
			)
		) );
	}

	// Register order meta
	if ( function_exists( 'edd_add_order_meta' ) ) {
		wp_register_meta_type( 'order', array(
			'table_name' => 'edd_ordermeta',
			'columns'    => array(
				'object_id' => 'edd_order_id'
			)
		) );
	}

	// Register order item meta
	if ( function_exists( 'edd_add_order_item_meta' ) ) {
		wp_register_meta_type( 'order-item', array(
			'table_name' => 'edd_order_itemmeta',
			'columns'    => array(
				'object_id' => 'edd_order_item_id'
			)
		) );
	}

	// Register order adjustment meta
	if ( function_exists( 'edd_add_order_adjustment_meta' ) ) {
		wp_register_meta_type( 'order-adjustment', array(
			'table_name' => 'edd_order_adjustmentmeta',
			'columns'    => array(
				'object_id' => 'edd_order_adjustment_id'
			)
		) );
	}

	// Register adjustment meta
	if ( function_exists( 'edd_add_adjustment_meta' ) ) {
		wp_register_meta_type( 'adjustment', array(
			'table_name' => 'edd_adjustmentmeta',
			'columns'    => array(
				'object_id' => 'edd_adjustment_id'
			)
		) );
	}

	// Register note meta
	if ( function_exists( 'edd_add_note_meta' ) ) {
		wp_register_meta_type( 'note', array(
			'table_name' => 'edd_notemeta',
			'columns'    => array(
				'object_id' => 'edd_note_id'
			)
		) );
	}

	// Register log meta
	if ( function_exists( 'edd_add_log_meta' ) ) {
		wp_register_meta_type( 'log', array(
			'table_name' => 'edd_logmeta',
			'columns'    => array(
				'object_id' => 'edd_log_id'
			)
		) );
	}
}
add_action( 'wp_register_meta_types', 'wp_meta_add_easy_digital_downloads_meta' );

/**
 * Register AffiliateWP's affiliate meta.
 *
 * @since 1.0.0
 */
function wp_meta_add_affiliatewp_meta() {

	// Bail if no AffWP
	if ( ! function_exists( 'affiliate_wp' ) ) {
		return;
	}

	// Register affiliate meta
	wp_register_meta_type( 'affiliate', array(
		'table_name' => 'affiliate_wp_affiliatemeta'
	) );
}
add_action( 'wp_register_meta_types', 'wp_meta_add_affiliatewp_meta' );

/**
 * Register Sugar Calendar's affiliate meta.
 *
 * @since 2.0.0
 */
function wp_meta_add_sugar_calendar_meta() {

	// Bail if no event meta function
	if ( ! function_exists( 'add_event_meta' ) ) {
		return;
	}

	// Register event meta
	wp_register_meta_type( 'event', array(
		'table_name' => 'sc_eventmeta',
		'columns'    => array(
			'object_id' => 'sc_event_id'
		)
	) );
}
add_action( 'wp_register_meta_types', 'wp_meta_add_sugar_calendar_meta' );
