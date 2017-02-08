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

// Ajax views
add_action( 'wp_ajax_edit-meta', 'wp_meta_ajax_edit_response' );