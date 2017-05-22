<?php


/**
 * Process meta data edit request
 *
 * @since 1.0.0
 *
 * @return void
 */
function wp_meta_ajax_edit_response() {

	if ( empty( $_POST['data'] ) ) {
		die( '-1' );
	}

	$data = array();

	wp_parse_str( $_POST['data'], $data );

	if ( empty( $data['wp-edit-meta-nonce'] ) ) {
		die( '-2' );
	}

	if ( ! wp_verify_nonce( $data['wp-edit-meta-nonce'], 'wp-edit-meta-nonce' ) ) {
		die( '-3' );
	}

	if ( ! current_user_can( 'manage_meta' ) ) {
		die( '-4' );
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

	! empty( $ret )
		? wp_send_json_success( array( 'success' => true, 'data' => $meta ) )
		: wp_send_json_error( array( 'success' => false, 'data' => $meta ) );
}

/**
 * Process meta data delete request
 *
 * @since 1.0.0
 *
 * @return void
 */
function wp_meta_ajax_delete_response() {

	if ( empty( $_POST['nonce'] ) ) {
		die( '-1' );
	}

	if ( empty( $_POST['meta_id'] ) || empty( $_POST['object_type'] ) ) {
		die( '-2' );
	}

	if ( ! wp_verify_nonce( $_POST['nonce'], 'wp-meta-delete' ) ) {
		die( '-3' );
	}

	if ( ! current_user_can( 'manage_meta' ) ) {
		die( '-4' );
	}

	$meta_id     = absint( $_POST['meta_id'] );
	$object_type = sanitize_key( $_POST['object_type'] );
	$meta        = get_meta( $object_type, $meta_id );

	$meta->delete()
		? wp_send_json_success( array( 'success' => true ) )
		: wp_send_json_error( array( 'success' => false ) );
}
