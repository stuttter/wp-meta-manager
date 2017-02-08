<?php

/**
 * Meta Cache
 *
 * @package Plugins/Meta/Cache
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Adds any meta from the given ids to the cache that do not already
 * exist in cache.
 *
 * @since 1.0.0
 * @access private
 *
 * @global wpdb $wpdb WordPress database abstraction object.
 *
 * @param array $ids ID list.
 */
function _prime_meta_caches( $ids = array(), $type = '' ) {
	global $wpdb;

	$type_object    = wp_get_meta_type( $type );
	$non_cached_ids = _get_non_cached_ids( $ids, $type . 'meta' );
	if ( ! empty( $non_cached_ids ) ) {
		$fresh_metas = $wpdb->get_results( sprintf( "SELECT * FROM {$type_object->table_name} WHERE id IN (%s)", join( ",", array_map( 'intval', $non_cached_ids ) ) ) );

		update_meta_cache( $fresh_metas, $type );
	}
}

/**
 * Updates meta in cache.
 *
 * @since 1.0.0
 *
 * @param array $metas Array of WP_Meta objects.
 */
function update_meta_cache( $metas = array(), $type = '' ) {

	// Bail if no metas
	if ( empty( $metas ) ) {
		return;
	}

	// Loop through metas & add them to cache group
	foreach ( $metas as $meta ) {
		wp_cache_add( $meta->id, $meta, $type . 'meta' );
	}
}

/**
 * Clean the meta cache
 *
 * @since 1.0.0
 *
 * @param int|WP_Meta $meta Meta ID or meta object to remove from the cache
 */
function clean_meta_cache( $meta, $type = '' ) {

	// Get meta, and bail if not found
	$meta = WP_Meta::get_instance( $meta );
	if ( empty( $meta ) || is_wp_error( $meta ) ) {
		return;
	}

	// Delete meta from cache group
	wp_cache_delete( $meta->id , $type . 'meta' );

	/**
	 * Fires immediately after meta has been removed from the object cache.
	 *
	 * @since 1.0.0
	 *
	 * @param int     $meta_id Meta ID.
	 * @param WP_Meta $meta    Meta object.
	 * @param string  $type    Meta type.
	 */
	do_action( 'clean_meta_cache', $meta->id, $meta, $type );

	wp_cache_set( 'last_changed', microtime(), $type . 'meta' );
}
