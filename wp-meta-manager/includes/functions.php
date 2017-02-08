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
		'table_name' => 'postmeta'
	) );

	// Register comment meta table
	wp_register_meta_type( 'comment', array(
		'table_name' => 'commentmeta'
	) );

	// Register term meta table
	wp_register_meta_type( 'term', array(
		'table_name' => 'termmeta'
	) );

	// Register user meta table
	wp_register_meta_type( 'user', array(
		'table_name' => 'usermeta'
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

	// Maybe instantiate the global
	if ( ! is_array( $wp_meta_types ) ) {
		$wp_meta_types = array();
	}

	// Sanitize the object type
	$object_type = sanitize_key( $object_type );

	if ( empty( $object_type ) || strlen( $object_type ) > 20 ) {
		_doing_it_wrong( __FUNCTION__, __( 'Meta type names must be between 1 and 20 characters in length.' ), '1.0.0' );
		return new WP_Error( 'meta_type_length_invalid', __( 'Meta type names must be between 1 and 20 characters in length.' ) );
	}

	// Add meta type object
	$wp_meta_types[ $object_type ] = new WP_Meta_Type( $object_type, $args );

	/**
	 * Fires after a meta type is registered.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $object_type        Meta type.
	 * @param WP_Meta_Type $object_type_object Meta type object.
	 * @param array        $args               Arguments used to register the meta type.
	 */
	do_action( 'wp_register_meta_type', $object_type, $wp_meta_types[ $object_type ], $args );

	return $wp_meta_types[ $object_type ];
}
