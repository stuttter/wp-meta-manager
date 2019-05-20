<?php

/**
 * Meta Manager Add New screen
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render Meta Manager Edit screen
 *
 * @since 1.0.0
 */
function wp_meta_manager_edit() {

	// Try to get the object type (default to post)
	$object_type = isset( $_GET['object_type'] )
		? sanitize_text_field( $_GET['object_type'] )
		: 'post';

	// Look for meta ID to edit
	$meta_id = ! empty( $_GET['meta_id'] )
		? absint( $_GET['meta_id'] )
		: 0;

	// Try to get meta being edited
	$meta = get_meta( $object_type, $meta_id );

	// Edit
	if ( ! empty( $meta ) ) {
		$object_id   = $meta->object_id;
		$meta_key    = $meta->meta_key;
		$meta_value  = $meta->meta_value;
		$action      = 'edit-meta';
		$button_text = esc_html__( 'Update Meta', 'wp-meta-manager' );

	// Add
	} else {
		$object_id   = '';
		$meta_key    = '';
		$meta_value  = '';
		$action      = 'add-meta';
		$button_text = esc_html__( 'Add Meta', 'wp-meta-manager' );
	} ?>

	<div class="wrap">
		<h1><?php printf( __( 'Add New %s Meta', 'wp-meta-manager' ), ucwords( $object_type ) ); ?></h1>
		<form method="post">
			<table class="form-table">

				<?php if ( ! empty( $meta_id ) ) : ?>

					<tr>
						<th scope="row">
							<label for="wp-meta-id"><?php esc_html_e( 'Meta ID', 'wp-meta-manager' ); ?></label>
						</th>
						<td>
							<input type="number" inputmode="numeric" disabled="disabled" name="meta_id" id="wp-meta-id" class="code" value="<?php echo absint( $meta_id ); ?>" />
							<p class="description"><?php esc_html_e( 'Cannot be changed. Used as unique cache key.', 'wp-meta-manager' ); ?></p>
						</td>
					</tr>

				<?php endif; ?>

				<tr>
					<th scope="row">
						<label for="wp-meta-object-id"><?php printf( esc_html__( '%s ID', 'wp-meta-manager' ), ucwords( $object_type ) ); ?></label>
					</th>
					<td>
						<input type="number" inputmode="numeric" min="1" max="<?php echo PHP_INT_MAX; ?>" name="object_id" id="wp-meta-object-id" class="code" value="<?php echo esc_attr( $object_id ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp-meta-key"><?php esc_html_e( 'Meta Key', 'wp-meta-manager' ); ?></label>
					</th>
					<td>
						<input type="text" name="meta_key" id="wp-meta-key" class="code" value="<?php echo esc_attr( $meta_key ); ?>" />
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp-meta-value"><?php esc_html_e( 'Meta Value', 'wp-meta-manager' ); ?></label><br/>
					</th>
					<td>
						<textarea name="meta_value" id="wp-meta-value" rows="10"><?php echo esc_textarea( $meta_value ); ?></textarea>
					</td>
				</tr>
			</table>

			<input type="hidden" name="action"      value="<?php echo esc_attr( $action ); ?>" />
			<input type="hidden" name="object_type" value="<?php echo esc_attr( $object_type ); ?>" />

			<?php

			wp_nonce_field( 'wp-meta-nonce', 'wp-meta-nonce' );
			submit_button( $button_text );

			?>
		</form>
	</div>

<?php
}
