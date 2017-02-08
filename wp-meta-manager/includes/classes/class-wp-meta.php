<?php

/**
 * Meta Object
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class WP_Meta {

	public $ID;
	public $object_id;
	public $key;
	public $value;
	public $object_type;

	public function __construct( $_ID = 0, $_object_type = '' ) {
	
		$this->ID = $_ID;
		$this->object_type = $_object_type;
	
		$meta = $this->get_meta();

		$this->key   = $meta->meta_key;
		$this->value = $meta->meta_value;

		return $meta;

	}

	private function get_meta() {

		$args = array(
			'meta_id' => $this->ID,
			'number'  => 1
		);

		$query = new WP_Meta_Data_Query( $args, $this->object_type );

		if( ! empty( $query->metas ) ) {

			return reset( $query->metas );
			
		}

		return false;

	}

}
