<?php
/**
 * Meta Manager Functions
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Register meta types
 *
 * @since 1.0.0
 */
function _wp_register_meta_types() {

	// Post meta table
	wp_register_meta_type( 'post', array(
		'edit_callback' => 'get_edit_post_link'
	) );

	// Comment meta table
	wp_register_meta_type( 'comment', array(
		'edit_callback' => 'get_edit_comment_link'
	) );

	// Term meta table
	wp_register_meta_type( 'term', array(
		'edit_callback' => 'get_edit_term_link'
	) );

	// User meta table
	wp_register_meta_type( 'user', array(
		'columns' => array(
			'meta_id' => 'umeta_id'
		),
		'edit_callback' => 'get_edit_user_link'
	) );

	// Network meta
	if ( is_multisite() ) {
		wp_register_meta_type( 'site', array(
			'global' => true,
			'edit_callback' => 'wp_meta_get_site_edit_link'
		) );
	}

	do_action( 'wp_register_meta_types' );
}

/**
 * Get a list of all registered meta type objects.
 *
 * @since 2.9.0
 *
 * @global array $wp_post_types List of meta types.
 *
 * @see register_post_type() for accepted arguments.
 *
 * @param array|string $args     Optional. An array of key => value arguments to match against
 *                               the meta type objects. Default empty array.
 * @param string       $output   Optional. The type of output to return. Accepts post type 'names'
 *                               or 'objects'. Default 'names'.
 * @param string       $operator Optional. The logical operation to perform. 'or' means only one
 *                               element from the array needs to match; 'and' means all elements
 *                               must match; 'not' means no elements may match. Default 'and'.
 * @return array A list of post type names or objects.
 */
function wp_get_meta_types( $args = array(), $output = 'names', $operator = 'and' ) {
	global $wp_meta_types;

	$field = ( 'names' === $output )
		? 'name'
		: false;

	return wp_filter_object_list( $wp_meta_types, $args, $operator, $field );
}

/**
 * Retrieves a meta type object by name.
 *
 * @since 1.0.0
 *
 * @global array $wp_meta_types List of meta types.
 *
 * @see wp_register_meta_type()
 *
 * @param string $object_type The name of a registered meta type.
 * @return WP_Meta_Type|null WP_Meta_Type object if it exists, null otherwise.
 */
function wp_get_meta_type( $object_type = '' ) {
	global $wp_meta_types;

	if ( ! is_scalar( $object_type ) || empty( $wp_meta_types[ $object_type ] ) ) {
		return null;
	}

	return $wp_meta_types[ $object_type ];
}


/**
 * Registers a meta type.
 *
 * Note: Meta type registrations should be hooked in as early as possible.
 * Also, all primary object types should be registered first.
 *
 * @since 1.0.0
 *
 * @global array $wp_meta_types List of meta types.
 *
 * @param string $object_type Meta type key. Must not exceed 20 characters and may
 *                          only contain lowercase alphanumeric characters, dashes,
 *                          and underscores. See sanitize_key().
 * @param array|string $args {
 *     Array or string of arguments for registering a meta type.
 *
 *     @type bool        $global                Whether this metadata is for a global object.
 *                                              Default is false.
 *     @type string      $tablename             The name of the meta-data table, un-prefixed.
 *                                              Default is value of $labels['name'].
 *     @type array       $columns               Array of database table columns.
 *                                              Keys: meta_id, object_id, meta_key, meta_value
 * }
 * @return WP_Meta_Type|WP_Error The registered meta type object, or an error object.
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

/**
 * Get meta
 *
 * @since 1.0.0
 */
function get_meta( $object_type = '', $meta = 0 ) {

	// Try to get meta
	if ( $meta instanceof WP_Meta ) {
		$_meta = $meta;
	} elseif ( is_object( $meta ) ) {
		$_meta = new WP_Meta( $meta );
	} else {
		$_meta = WP_Meta::get_instance( $object_type, $meta );
	}

	// Bail if no meta
	if ( empty( $_meta ) ) {
		return null;
	}

	/**
	 * Fires after a meta is retrieved.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Meta $_meta Meta data.
	 */
	$_meta = apply_filters( 'get_meta', $_meta );

	return $_meta;
}

/**
 * Add new meta data
 *
 * @since 1.0.0
 */
function wp_add_meta( $object_type = '', $args = array() ) {
	global $wpdb;

	// Get the meta type
	$type_object = wp_get_meta_type( $object_type );

	// Map columns
	$data = array(
		$type_object->columns['meta_key']   => $args['meta_key'],
		$type_object->columns['meta_value'] => $args['meta_value'],
		$type_object->columns['object_id']  => $args['object_id']
	);

	// Insert into database
	$ret = $wpdb->insert( $type_object->table_name, $data, array( '%s', '%s', '%d' ) );

	/**
	 * Fires after a new meta row is created.
	 *
	 * @since 1.0.0
	 *
	 * @param string       $object_type Meta type.
	 * @param array        $args        Arguments used to create the meta data.
	 * @param WP_Meta_Type $type_object Meta type object.
	 */
	do_action( 'wp_add_meta', $object_type, $args, $type_object );

	return $ret;
}

/**
 * Retrieve the URL to the edit site screen
 *
 * @since 1.0.0
 */
function wp_meta_get_site_edit_link( $site_id = 0 ) {
	return network_admin_url( 'site-info.php?id=' . absint( $site_id ) );
}
