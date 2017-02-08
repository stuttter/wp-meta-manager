<?php

/**
 * Meta Type Object
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class WP_Meta_Type {

	public $object_type;
	public $columns;

	public function __construct( $object_type = '', $args = array() ) {
		global $wpdb;

		// Default columns
		$columns = array(
			'meta_id'    => 'meta_id',
			'object_id'  => $object_type . '_id',
			'meta_key'   => 'meta_key',
			'meta_value' => 'meta_value'
		);

		// Parse the arguments
		$r = wp_parse_args( $args, array(
			'global'     => false,
			'table_name' => $object_type . 'meta',
			'columns'    => $columns,
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
