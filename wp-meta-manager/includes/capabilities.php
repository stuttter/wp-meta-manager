<?php

/**
 * Meta Manager Capabilities
 *
 * @package Plugins/Meta/Capabilities
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Maps meta manager capabilities
 *
 * @since 0.1.0
 *
 * @param  array   $caps     Capabilities for meta capability
 * @param  string  $cap      Capability name
 * @param  int     $user_id  User id
 * @param  array   $args     Arguments
 *
 * @return array   Actual capabilities for meta capability
 */
function wp_meta_manager_meta_caps( $caps = array(), $cap = '', $user_id = 0, $args = array() ) {

	// What capability is being checked?
	switch ( $cap ) {

		// Manage
		case 'manage_meta' :
			$caps = array( 'manage_options' );
			break;
	}

	return apply_filters( 'wp_meta_manager_meta_caps', $caps, $cap, $user_id, $args );
}
