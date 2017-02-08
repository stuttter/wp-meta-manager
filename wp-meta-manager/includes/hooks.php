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

// Add new meta handling
add_action( 'admin_init', 'wp_meta_process_add_meta' );

// Ajax processing
add_action( 'wp_ajax_edit_meta', 'wp_meta_ajax_edit_response' );
add_action( 'wp_ajax_delete_meta', 'wp_meta_ajax_delete_response' );