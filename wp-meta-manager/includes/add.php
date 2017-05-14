<?php

/**
 * Meta Manager Add New screen
 *
 */

// Exit if accessed directly
defined( 'ABSPATH' ) || exit;

/**
 * Render Meta Manager Add New screen
 *
 * @since 1.0.0
 */
function wp_meta_manager_add_new() {

	// Try to get the object type (default to post)
	$object_type = isset( $_GET['object_type'] )
		? sanitize_text_field( $_GET['object_type'] )
		: 'post'; ?>

	<div class="wrap">
		<h1><?php printf( __( 'Add New %s Meta', 'wp-meta-manager' ), ucwords( $object_type ) ); ?></h1>
		<form method="post">
			<table class="form-table">
				<tr>
					<th scope="row">
						<label for="wp-meta-add-object-id"><?php _e( 'Object ID', 'wp-meta-manager' ); ?></label>
					</th>
					<td>
						<input type="text" name="object_id" id="wp-meta-add-object-id" value=""/>
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp-meta-add-meta-key"><?php _e( 'Meta Key', 'wp-meta-manager' ); ?></label>
					</th>
					<td>
						<input type="text" name="meta_key" id="wp-meta-add-meta-key" value=""/>	
					</td>
				</tr>
				<tr>
					<th scope="row">
						<label for="wp-meta-add-meta-value"><?php _e( 'Meta Value', 'wp-meta-manager' ); ?></label><br/>
					</th>
					<td>
						<textarea name="meta_value" id="wp-meta-add-meta-value" rows="10"></textarea>
					</td>
				</tr>
				
			</table>

			<input type="hidden" name="action" value="add_meta" />
			<input type="hidden" name="object_type" value="<?php echo esc_attr( $object_type ); ?>" />

			<?php

			wp_nonce_field( 'wp-add-meta-nonce', 'wp-add-meta-nonce' );
			submit_button( esc_html__( 'Add Meta', 'wp-meta-manager' ) );

			?>

			<span class="spinner"></span>

		</form>
	</div>

<?php 
}
