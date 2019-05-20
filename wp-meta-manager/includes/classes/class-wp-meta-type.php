<?php

/**
 * Meta Type Object
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class WP_Meta_Type {

	/**
	 * Sanitized string of the type of meta this is.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $object_type = '';

	/**
	 * Array of database column names.
	 *
	 * These are usually automatically built based on the object_type, but in
	 * some cases (like wp_usermeta) a column name may deviate from that pattern.
	 *
	 * @since 1.0.0
	 *
	 * @var array
	 */
	public $columns = array();

	/**
	 * Array of labels.
	 *
	 * These are usually automatically built based on the object_type, but you
	 * may want to define these to provide additional context.
	 *
	 * @since 2.0.0
	 *
	 * @var array
	 */
	public $labels = array();

	/**
	 * Callback function to retrieve the edit URL for an object.
	 *
	 * Object ID (e.g. post ID) is passed as a parameter to the callback function.
	 *
	 * @since 1.0.0
	 *
	 * @var string
	 */
	public $edit_callback = '';

	/**
	 * Setup the object properties
	 *
	 * @since 1.0.0
	 *
	 * @see wp_register_meta_type()
	 *
	 * @global WPDB $wpdb
	 *
	 * @param string $object_type
	 * @param array  $args
	 */
	public function __construct( $object_type = '', $args = array() ) {
		global $wpdb;

		// Sanitize the object type
		$object_type = sanitize_key( $object_type );

		// Parse root arguments
		$r = wp_parse_args( $args, array(
			'global'        => false,
			'table_name'    => $object_type . 'meta',
			'columns'       => array(),
			'labels'        => array(),
			'edit_callback' => ''
		) );

		// Parse columns argument
		$r['columns'] = wp_parse_args( $r['columns'], array(
			'meta_id'    => 'meta_id',
			'object_id'  => $object_type . '_id',
			'meta_key'   => 'meta_key',
			'meta_value' => 'meta_value'
		) );

		// Default labels
		$default_singular = implode( ' ', array_map( 'ucfirst', explode( '-', $object_type ) ) );
		$default_plural   = $default_singular . 's';

		// Parse labels argument
		$r['labels'] = wp_parse_args( $r['labels'], array(
			'singular' => $default_singular,
			'plural'   => $default_plural
		) );

		// Get prefix for global/site meta
		$prefix = ( true === $r['global'] )
			? $wpdb->base_prefix
			: $wpdb->get_blog_prefix();

		// Set object properties
		$this->table_name    = $prefix . $r['table_name'];
		$this->object_type   = $object_type;
		$this->columns       = array_filter( $r['columns'] );
		$this->labels        = array_filter( $r['labels'] );
		$this->edit_callback = $r['edit_callback'];
	}
}
