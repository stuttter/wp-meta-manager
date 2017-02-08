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
 * @since 1.0
 */
function wp_meta_manager_admin_menu() {

	$hook = add_management_page( __( 'Meta Manager', 'wp-meta-manager' ), __( 'Meta Manager', 'wp-meta-manager' ), 'manage_options', 'wp-meta-manager', 'wp_meta_manager_admin' );

	add_action( 'load-' . $hook, 'wp_meta_manager_admin_help' );
	add_action( 'load-' . $hook, 'wp_meta_manager_admin_scripts' );

}

/**
 * Load Meta Manager scripts
 *
 * @since 1.0
 */
function wp_meta_manager_admin_scripts() {

	wp_enqueue_script( 'wp-meta-manager-admin', wp_meta_manager_get_plugin_url() . 'assets/js/admin.js', array( 'jquery' ) );

}

/**
 * Render Meta Manager admin
 *
 * @since 1.0
 */
function wp_meta_manager_admin() {

	if( ! empty( $_GET['view'] ) && 'add-new' == $_GET['view'] ) {
		wp_meta_manager_add_new();
		return;
	}

	add_thickbox();
	$tab      = isset( $_GET['tab'] ) ? sanitize_text_field( $_GET['tab'] ) : 'post';
	$base_url = admin_url( 'tools.php?page=wp-meta-manager' );

?>
	<div class="wrap">
		<h1 class="wp-heading-inline"><?php esc_html_e( 'Meta Manager', 'wp-meta-manager' ); ?></h1>
		<a href="<?php echo esc_url( add_query_arg( array( 'view' => 'add-new', 'object_type' => $tab ), $base_url ) ); ?>" class="page-title-action"><?php printf( __( 'Add New %s Meta', 'wp-meta-manager' ), ucwords( $tab ) ); ?></a>
		<h2 class="nav-tab-wrapper"><?php wp_meta_admin_tabs( $tab ); ?></h2>
<?php
		$page       = isset( $_REQUEST['page'] ) ? absint( $_REQUEST['page'] ) : 1;
		$list_table = new WP_Meta_List_table();
		$list_table->object_type = $tab;
		$list_table->table_name  = wp_get_meta_type( $tab )->table_name;
		$list_table->prepare_items();
?>
		<form id="wp-meta-data" method="get">
			<input type="hidden" name="page" value="<?php echo esc_attr( $page ); ?>" />

			<?php $list_table->display(); ?>
		</form>
		<?php if( $list_table->items ) : ?>
			<?php foreach( $list_table->items as $item ) : ?>
				<?php echo $list_table->edit_form( $item ); ?>
			<?php endforeach; ?>
		<?php endif; ?>
	</div>
<?php
}

/**
 * Render Meta Manager admin notices
 *
 * @since 1.0
 */
function wp_meta_manager_admin_notices() {

	if( empty( $_GET['wp-meta-message'] ) ) {
		return;
	}

	switch( $_GET['wp-meta-message'] ) {

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
 * @since 1.0
 */
function wp_meta_manager_admin_help() {

}

/**
 * Output the tabs in the admin area
 *
 * @since 1.0
 *
 * @param string $active_tab Name of the tab that is active
 */
function wp_meta_admin_tabs( $active_tab = '' ) {
	echo wp_meta_get_admin_tab_html( $active_tab );
}

/**
 * Output the tabs in the admin area
 *
 * @since 1.0
 *
 * @param string $active_tab Name of the tab that is active
 */
function wp_meta_get_admin_tab_html( $active_tab = '' ) {

	// Declare local variables
	$tabs_html    = '';
	$idle_class   = 'nav-tab';
	$active_class = 'nav-tab nav-tab-active';

	// Setup core admin tabs
	$tabs = wp_meta_get_admin_tabs();

	// Loop through tabs and build navigation
	foreach ( $tabs as $tab ) {

		// Setup tab HTML
		$is_current = (bool) ( $tab == $active_tab );
		$tab_class  = $is_current ? $active_class : $idle_class;
		$tab_url    = get_admin_url( '', add_query_arg( array( 'tab' => $tab ), 'tools.php?page=wp-meta-manager' ) );
		$tabs_html .= '<a href="' . esc_url( $tab_url ) . '" class="' . esc_attr( $tab_class ) . '">' . esc_html( ucfirst( $tab ) ) . '</a>';
	}

	// Output the tabs
	return $tabs_html;
}

/**
 * Return possible admin tabs
 *
 * @since 1.0
 *
 * @return array
 */
function wp_meta_get_admin_tabs() {
	$types = wp_get_meta_types( array(), 'objects' );
	$tabs  = wp_list_pluck( $types, 'object_type' );

	return apply_filters( 'wp_meta_admin_tabs', $tabs );
}

/**
 * Process meta data edit request
 *
 * @since 1.0
 *
 * @return void
 */
function wp_meta_ajax_edit_response() {

	if( empty( $_POST['data'] ) ) {
		die( '-1' );
	}

	wp_parse_str( $_POST['data'], $data );

	if( empty( $data['wp-edit-meta-nonce'] ) ) {
		die( '-2' );
	}

	if ( ! wp_verify_nonce( $data['wp-edit-meta-nonce'], 'wp-edit-meta-nonce' ) ) {
		die( '-3' );
	}

	$meta_id     = absint( $data['meta_id'] );
	$object_type = sanitize_key( $data['object_type'] );
	$meta_key    = wp_unslash( $data['meta_key'] );
	$meta_value  = wp_unslash( $data['meta_value'] );
	$meta_value  = sanitize_meta( $meta_key, $meta_value, $object_type );
	$object_id   = absint( $data['object_id'] );
	$meta        = get_meta( $object_type, $meta_id );

	$ret = $meta->update( array(
		'meta_key'   => $meta_key,
		'meta_value' => $meta_value,
		'object_id'  => $object_id,
	) );

	if( $ret ) {

		wp_send_json_success( array( 'success' => true, 'data' => $meta ) );

	} else {

		wp_send_json_error( array( 'success' => false, 'data' => $meta ) );

	}

}

/**
 * Process meta data delete request
 *
 * @since 1.0
 *
 * @return void
 */
function wp_meta_ajax_delete_response() {

	if( empty( $_POST['nonce'] ) ) {
		die( '-1' );
	}

	if( empty( $_POST['meta_id'] ) || empty( $_POST['object_type'] ) ) {
		die( '-2' );
	}

	if ( ! wp_verify_nonce( $_POST['nonce'], 'wp-meta-delete' ) ) {
		die( '-3' );
	}

	$meta_id     = absint( $_POST['meta_id'] );
	$object_type = sanitize_key( $_POST['object_type'] );
	$meta        = get_meta( $object_type, $meta_id );

	$ret = $meta->delete();

	if( $ret ) {

		wp_send_json_success( array( 'success' => true ) );

	} else {

		wp_send_json_error( array( 'success' => false ) );

	}

}

/**
 * Process meta data delete request
 *
 * @since 1.0
 *
 * @return void
 */
function wp_meta_process_add_meta() {

	if( empty( $_POST['action'] ) || 'add_meta' !== $_POST['action'] ) {
		return;
	}

	if( empty( $_POST['wp-add-meta-nonce'] ) ) {
		return;
	}

	if( empty( $_POST['object_type'] ) ) {
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

	if( wp_add_meta( $object_type, $args ) ) {

		$message = 'success';

	} else {

		$message = 'failure';

	}

	wp_redirect( admin_url( 'tools.php?page=wp-meta-manager&tab=' . $object_type . '&wp-meta-message=' . $message ) ); exit;

}