<?php

/**
 * Meta Manager Admin
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

// Admin menus
add_action( 'admin_menu', 'wp_meta_manager_admin_menu' );

// Register meta tables
add_action( 'admin_init', '_wp_register_meta_types', 0 );