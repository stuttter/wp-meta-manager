<?php

/**
 * Meta Object
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

class WP_Meta {

	/**
	 * @var int $id ID of meta data
	 */
	public $id = 0;

	/**
	 * @var int $object_id ID of object
	 */
	public $object_id = 0;

	/**
	 * @var string $object_type Type of object
	 */
	public $object_type = '';

	/**
	 * @var string $key Meta data key
	 */
	public $key = '';

	/**
	 * @var mixed $value Meta data value
	 */
	public $value = null;

	/**
	 * Get a WP_Meta instance
	 *
	 * @since 1.0.0
	 *
	 * @global WPDB $wpdb
	 *
	 * @param string $type
	 * @param int $meta_id
	 *
	 * @return WP_Meta|boolean
	 */
	public static function get_instance( $type = '', $meta_id = 0 ) {
		global $wpdb;

		// Bail if no meta ID
		$meta_id = (int) $meta_id;
		if ( empty( $meta_id ) ) {
			return false;
		}

		// Sanitize the type
		$type = sanitize_key( $type );

		// Check cache
		$cache_group = "{$type}_meta_manager";
		$_meta       = wp_cache_get( $meta_id, $cache_group );

		// Cache miss
		if ( false === $_meta ) {

			// Query for meta
			$type_object = wp_get_meta_type( $type );
			$result      = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$type_object->table_name} WHERE {$type_object->columns['meta_id']} = %d LIMIT 1", $meta_id ) );

			// Bail if no meta found
			if ( empty( $result ) || is_wp_error( $result ) ) {
				return false;
			}

			// Setup for mapping values to columns
			$_meta = self::normalize( $type, $result );

			// Cache object
			wp_cache_add( $meta_id, $_meta, $cache_group );
		}

		// Return WP_Meta object
		return new WP_Meta( $_meta );
	}

	/**
	 * Normalize values to columns
	 *
	 * @since 1.0.0
	 *
	 * @param string $type
	 * @param object $meta
	 * @return object
	 */
	public static function normalize( $type = '', $meta = '' ) {
		$type        = sanitize_key( $type );
		$type_object = wp_get_meta_type( $type );
		$_meta       = new stdClass();
		$map         = $type_object->columns;

		// Loop through database results
		foreach ( $meta as $meta_key => $meta_value ) {

			// Loop through meta column mappings
			foreach ( $map as $map_key => $map_value ) {

				// Map value to correct index
				if ( $meta_key === $map_value ) {
					$_meta->$map_key = $meta_value;
				}
			}
		}

		// Add type to object
		$_meta->object_type = $type;

		return $_meta;
	}

	/**
	 * Creates a new WP_Meta object.
	 *
	 * Will populate object properties from the object provided and assign other
	 * default properties based on that information.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param WP_Meta|object $meta A meta object.
	 */
	public function __construct( $meta ) {
		foreach ( get_object_vars( $meta ) as $key => $value) {
			$this->{$key} = $value;
		}
	}

	/**
	 * Getter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key Property to get.
	 * @return mixed Value of the property. Null if not available.
	 */
	public function __get( $key ) {
		switch ( $key ) {
			case 'ID':
			case 'id':
			case 'meta_id':
				return absint( $this->id );

			case 'object_id':
			case $this->object_type . '_id':
				return (int) $this->object_id;

			case 'key':
			case 'meta_key':
				return $this->key;

			case 'value':
			case 'meta_value':
				return $this->value;
		}

		return null;
	}

	/**
	 * Isset-er.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key Property to check if set.
	 * @return bool Whether the property is set.
	 */
	public function __isset( $key ) {
		switch ( $key ) {
			case 'ID':
			case 'id':
			case 'meta_id':
				return true;
		}

		return false;
	}

	/**
	 * Setter.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $key   Property to set.
	 * @param mixed  $value Value to assign to the property.
	 */
	public function __set( $key, $value ) {
		switch ( $key ) {
			case 'ID':
			case 'id':
			case 'meta_id':
				$this->id = (int) $value;
				break;
			case 'object_id':
				$this->object_id = (int) $value;
				break;
			default:
				$this->{$key} = $value;
		}
	}

	/**
	 * Update meta.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param array $args
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function update( $args = array() ) {
		global $wpdb;

		$type_object = wp_get_meta_type( $this->object_type );

		$ret = $wpdb->update(
			$type_object->table_name,
			array(
				$type_object->columns['meta_key']   => $args['meta_key'],
				$type_object->columns['meta_value'] => $args['meta_value'],
				$type_object->columns['object_id']  => $args['object_id']
			),
			array(
				$type_object->columns['meta_id'] => $this->id
			),
			array(
				'%s',
				'%s',
				'%d'
			)
		);

		wp_clean_meta_cache( $this->object_type, $this->id );

		return $ret;
	}

	/**
	 * Update meta.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string $object_type
	 * @param array $args
	 * @return bool|WP_Error True on success, WP_Error on failure
	 */
	public function delete() {
		global $wpdb;

		$type_object = wp_get_meta_type( $this->object_type );

		$ret = $wpdb->delete(
			$type_object->table_name,
			array(
				$type_object->columns['meta_id'] => $this->id
			),
			array(
				'%d'
			)
		);

		wp_clean_meta_cache( $this->object_type, $this->id );

		return $ret;
	}
}
