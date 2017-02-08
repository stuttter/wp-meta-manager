<?php

/**
 * Meta Manager Admin
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register meta types
 *
 * @since 1.0
 */
function _wp_register_meta_types() {

	// Register post meta table
	wp_register_meta_type( 'post', array(
		'name'               => 'postmeta',
		'columns'            => array(
			'primary_column' => 'meta_id',
			'object_id'      => 'post_id',
			'key'            => 'meta_key',
			'value'          => 'meta_value'
		)
	) );

	// Register comment meta table
	wp_register_meta_type( 'comment', array(
		'name'               => 'commentmeta',
		'columns'            => array(
			'primary_column' => 'meta_id',
			'object_id'      => 'comment_id',
			'key'            => 'meta_key',
			'value'          => 'meta_value'
		)
	) );

	// Register term meta table
	wp_register_meta_type( 'term', array(
		'name'               => 'termmeta',
		'columns'            => array(
			'primary_column' => 'meta_id',
			'object_id'      => 'term_id',
			'key'            => 'meta_key',
			'value'          => 'meta_value'
		)
	) );

	// Register user meta table
	wp_register_meta_type( 'user', array(
		'name'               => 'usermeta',
		'columns'            => array(
			'primary_column' => 'umeta_id',
			'object_id'      => 'user_id',
			'key'            => 'meta_key',
			'value'          => 'meta_value'
		)
	) );

	do_action( 'wp_register_meta_types' );

}

/**
 * Get meta tables
 *
 * @since 1.0
 */
function wp_get_meta_types() {

	global $wp_meta_types;

	if( ! did_action( 'wp_register_meta_types' ) ) {
		// doing it wrong notice
	}

	return (array) apply_filters( 'wp_meta_types', $wp_meta_types );
}

/**
 * Register meta table
 *
 * @since 1.0
 */
function wp_get_meta_type( $object_type = '' ) {

	$types = wp_get_meta_types();
	
	if( ! isset( $types[ $object_type ] ) ) {
		return false;
	}

	return $types[ $object_type ];
}

/**
 * Register meta table
 *
 * @since 1.0
 */
function wp_register_meta_type( $object_type = '', $args = array() ) {
	
	global $wp_meta_types;

	if( empty( $object_type ) ) {
		return false;
	}

	$defaults = array(
		'name'    => '',
		'columns' => array()
	);

	$args = wp_parse_args( $args, $defaults );

	if( empty( $args['name'] ) || empty( $args['columns'] ) ) {
		return false;
	}

	$wp_meta_types[ $object_type ] = new WP_Meta_Type( $object_type, $args );

	do_action( 'wp_register_meta_type', $object_type, $args );

	return true;

}