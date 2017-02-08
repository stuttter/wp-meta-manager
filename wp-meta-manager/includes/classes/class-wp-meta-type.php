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
	public $object_type;

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

		// Parse root arguments
		$r = wp_parse_args( $args, array(
			'global'     => false,
			'table_name' => $object_type . 'meta',
			'columns'    => array()
		) );

		// Parse columns argument
		$r['columns'] = wp_parse_args( $r['columns'], array(
			'meta_id'    => 'meta_id',
			'object_id'  => $object_type . '_id',
			'meta_key'   => 'meta_key',
			'meta_value' => 'meta_value'
		) );

		// Get prefix for global/site meta
		$prefix = ( true === $r['global'] )
			? $wpdb->base_prefix
			: $wpdb->get_blog_prefix();

		// Set object properties
		$this->table_name  = $prefix . $r['table_name'];
		$this->object_type = sanitize_key( $object_type );
		$this->columns     = array_filter( $r['columns'] );
	}
}
