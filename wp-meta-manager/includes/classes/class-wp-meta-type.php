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
	public $primary_column_id;

	public function __construct( $object_type = '', $args = array() ) {

		$defaults = array(
			'name'              => '',
			'columns'           => array(),
		);

		$args = wp_parse_args( $args, $defaults );

		$this->name              = $args['name'];
		$this->object_type       = $object_type;
		$this->columns           = $args['columns'];
		$this->primary_column_id = $args['columns']['primary_column_id'];

	}

}