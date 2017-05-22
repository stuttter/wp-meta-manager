<?php

/**
 * Meta Manager Admin
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Adds the Metadata menu under Tools
 *
 * @since 1.0.0
 */
function wp_meta_manager_admin_menu() {

	$hook = add_management_page( __( 'Meta Manager', 'wp-meta-manager' ), __( 'Meta Manager', 'wp-meta-manager' ), 'manage_meta', 'wp-meta-manager', 'wp_meta_manager_admin' );

	add_action( 'load-' . $hook, 'wp_meta_manager_admin_help'    );
	add_action( 'load-' . $hook, 'wp_meta_manager_admin_scripts' );
}

/**
 * Load Meta Manager scripts
 *
 * @since 1.0.0
 */
function wp_meta_manager_admin_scripts() {

	// Script data
	$url = wp_meta_manager_get_plugin_url();
	$ver = wp_meta_manager_get_asset_version();

	// Enqueues
	wp_enqueue_script( 'wp-meta-manager-admin', $url . 'assets/js/admin.js',   array( 'jquery' ), $ver );
	wp_enqueue_style( 'wp-meta-manager-admin',  $url . 'assets/css/admin.css', array(),           $ver );
}

/**
 * Render Meta Manager admin
 *
 * @since 1.0.0
 */
function wp_meta_manager_admin() {

	// Maybe return add-new page
	if ( ! empty( $_GET['view'] ) && ( 'edit' === $_GET['view'] ) ) {
		wp_meta_manager_edit();
		return;
	}

	// Get tab
	$tab = isset( $_GET['tab'] )
		? sanitize_key( $_GET['tab'] )
		: 'post';

	// Look for registered meta type
	$object_type = wp_get_meta_type( $tab );

	// Fallback to 'post' meta type if $_GET is weird
	if ( null === $object_type ) {
		$tab         = 'post';
		$object_type = wp_get_meta_type( $tab );
	}

	// "Add New" URL
	$add_new_url = add_query_arg( array(
		'view'        => 'edit',
		'object_type' => $tab
	), menu_page_url( 'wp-meta-manager', false ) );

	// Prepare the List Table UI
	$list_table              = new WP_Meta_List_table();
	$list_table->object_type = $tab;
	$list_table->table_name  = $object_type->table_name;
	$list_table->prepare_items(); ?>

	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Meta Manager', 'wp-meta-manager' ); ?></h1>
		<a href="<?php echo esc_url( $add_new_url ); ?>" class="page-title-action"><?php printf( esc_html__( 'Add New %s Meta', 'wp-meta-manager' ), ucwords( $tab ) ); ?></a>
		<h2 class="nav-tab-wrapper"><?php wp_meta_admin_tabs( $tab ); ?></h2>
		<form id="wp-meta-data" method="get">
			<input type="hidden" name="page" value="wp-meta-manager" />
			<?php $list_table->search_box( esc_attr__( 'Search', 'wp-meta-data' ), 'wp-meta-data' ); ?>
			<?php $list_table->display(); ?>
		</form>
	</div>

<?php
}

/**
 * Render Meta Manager admin notices
 *
 * @since 1.0.0
 */
function wp_meta_manager_admin_notices() {

	// Bail if no message
	if ( empty( $_GET['wp-meta-message'] ) ) {
		return;
	}

	// Bail if user cannot manage meta
	if ( ! current_user_can( 'manage_meta' ) ) {
		return;
	}

	switch ( $_GET['wp-meta-message'] ) {
		case 'success' :
			echo '<div class="updated"><p>' . __( 'Meta data added.', 'wp-meta-manager' ) . '</p></div>';
			break;

		case 'failure' :
			echo '<div class="error"><p>' . __( 'Meta data could not be added.', 'wp-meta-manager' ) . '</p></div>';
			break;
	}
}

/**
 * Render Meta Manager admin help
 *
 * @since 1.0.0
 */
function wp_meta_manager_admin_help() {

}

/**
 * Output the tabs in the admin area
 *
 * @since 1.0.0
 *
 * @param string $active_tab Name of the tab that is active
 */
function wp_meta_admin_tabs( $active_tab = '' ) {
	echo wp_meta_get_admin_tab_html( $active_tab );
}

/**
 * Output the tabs in the admin area
 *
 * @since 1.0.0
 *
 * @param string $active_tab Name of the tab that is active
 */
function wp_meta_get_admin_tab_html( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = array();
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';
	$base_url     = menu_page_url( 'wp-meta-manager', false );

	// Setup core admin tabs
	$tabs = wp_meta_get_admin_tabs();

	// Loop through tabs and build navigation
	foreach ( $tabs as $tab ) {

		// Setup tab HTML
		$is_current  = (bool) ( $tab === $active_tab );
		$tab_class   = $is_current ? $active_class : $idle_class;
		$tab_url     = add_query_arg( array( 'tab' => $tab, ), $base_url );
		$tabs_html[] = '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( ucfirst( $tab ) ) . '</a>';
	}

	// Return the tab ID
	return implode( '', $tabs_html );
}

/**
 * Return possible admin tabs
 *
 * @since 1.0.0
 *
 * @return array
 */
function wp_meta_get_admin_tabs() {
	$types = wp_get_meta_types( array(), 'objects' );
	$tabs  = wp_list_pluck( $types, 'object_type' );

	return apply_filters( 'wp_meta_admin_tabs', $tabs );
}

/**
 * Process meta data delete request
 *
 * @since 1.0.0
 *
 * @return void
 */
function wp_meta_process_add_meta() {

	if ( empty( $_POST['action'] ) || 'add_meta' !== $_POST['action'] ) {
		return;
	}

	if ( empty( $_POST['wp-add-meta-nonce'] ) ) {
		return;
	}

	if ( empty( $_POST['object_type'] ) ) {
		return;
	}

	if ( ! current_user_can( 'manage_meta' ) ) {
		return;
	}

	if ( ! wp_verify_nonce( $_POST['wp-add-meta-nonce'], 'wp-add-meta-nonce' ) ) {
		wp_die( __( 'Nonce verification failed', 'wp-meta-manager' ), __( 'Error', 'wp-meta-manager' ), array( 'response' => 403 ) );
	}

	$object_type = sanitize_key( $_POST['object_type'] );
	$object_id   = absint( $_POST['object_id'] );
	$meta_key    = wp_unslash( $_POST['meta_key'] );
	$meta_value  = wp_unslash( $_POST['meta_value'] );
	$meta_value  = sanitize_meta( $meta_key, $meta_value, $object_type );
	$args        = array(
		'object_id'  => $object_id,
		'meta_key'   => $meta_key,
		'meta_value' => $meta_value
	);

	$message = wp_add_meta( $object_type, $args )
		? 'success'
		: 'failure';

	wp_redirect( admin_url( 'tools.php?page=wp-meta-manager&tab=' . $object_type . '&wp-meta-message=' . $message ) ); exit;
}
