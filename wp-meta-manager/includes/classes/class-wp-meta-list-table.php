<?php

/**
 * WP Meta List Table
 *
 * @since      1.0
 *
 * @see WP_List_Table
 */

// Include the main list table class if it's not included yet
if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once ABSPATH . 'wp-admin/includes/class-wp-list-table.php';
}

if ( class_exists( 'WP_List_Table' ) ) :

/**
 * Meta data list table
 *
 * @since 1.0
 */
class WP_Meta_List_table extends WP_List_Table {

	public $object_type;
	public $table_name;

	/**
	 * The main constructor method
	 *
	 * @since 1.0
	 */
	public function __construct( $args = array() ) {
		$args = array(
			'singular' => 'meta_row',
			'plural'   => 'meta_rows',
			'ajax'     => false
		);
		parent::__construct( $args );
	}

	/**
	 * Setup the list-table columns
	 *
	 * @since 1.0
	 *
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'meta_id'     => esc_html__( 'Meta ID',    'wp-meta-manager' ),
			'object_id'   => esc_html__( 'Object ID',  'wp-meta-manager' ),
			'meta_key'    => esc_html__( 'Meta Key',   'wp-meta-manager' ),
			'meta_value'  => esc_html__( 'Meta Value', 'wp-meta-manager' ),
		);
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0
	 *
	 * @return array An associative array
	 */
	public function get_sortable_columns() {
		return array(
			'meta_id'    => array( 'meta_id',    false ),
			'object_id'  => array( 'object_id',  false ),
			'meta_key'   => array( 'meta_key',   false ),
			'meta_value' => array( 'meta_value', false ),
		);
	}

	/**
	 * Setup the bulk actions
	 *
	 * @since 1.0
	 *
	 * @return array An associative array containing all the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => esc_html__( 'Delete', 'wp-meta-manager' )
		);
	}

	/**
	 * Output the check-box column for bulk actions (if we implement them)
	 *
	 * @since 1.0
	 */
	public function column_cb( $item = '' ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			$this->_args['singular'],
			$item->id
		);
	}

	/**
	 * Output the meta_id column with row actions
	 *
	 * @since 1.0
	 */
	public function column_meta_id( $item = '' ) {
		
		$edit_url   = '#TB_inline?width=600&height=600&inlineId=wp-meta-edit-' . $item->id;
		$delete_url = add_query_arg( array(
			'object_type' => $item->object_type,
			'action'  => 'delete-meta',
			'nonce'   => wp_create_nonce( 'wp-delete-meta' )
		) );

		$actions = array(
			'edit'   => '<a href="' . esc_url( $edit_url ) . '" class="wp-meta-action-link thickbox">' . __( 'Edit', 'wp-meta-manager' ) . '</a>',
			'delete' => '<a href="' . esc_url( $delete_url ) . '"wp-meta-action-link delete" data-id="' . esc_attr( $item->id ) . '" data-object-type="' . esc_attr( $item->object_type ) . '">' . __( 'Delete', 'wp-meta-manager' ) . '</a>'
		);

		return $item->id . '<div class="row-actions">' . $this->row_actions( $actions, true ) . '</div>';

	}

	/**
	 * Output the meta_id column
	 *
	 * @since 1.0
	 */
	public function column_object_id( $item = '' ) {
		return $item->object_id;
	}

	/**
	 * Output the meta_key column
	 *
	 * @since 1.0
	 */
	public function column_meta_key( $item = '' ) {
		return $item->meta_key;
	}

	/**
	 * Output the meta_value column
	 *
	 * @since 1.0
	 */
	public function column_meta_value( $item = '' ) {
		return is_serialized( $item->meta_value )
			? '<code>serialized</code>'
			: '<code>' . $item->meta_value . '</code>';
	}

	/**
	 * Handle bulk action requests
	 *
	 * @since 1.0
	 */
	public function process_bulk_action() {
		switch ( $this->current_action() ) {
			case 'delete' :
				// Handle deletion
				break;
		}
	}

	/**
	 * Get the total number of rows
	 *
	 * @todo  Cache this
	 * @since 1.0
	 */
	public function get_total_items() {
		global $wpdb;
		return (int) $wpdb->get_var( "SELECT COUNT(*) FROM $this->table_name WHERE 1=1;" );

	}

	/**
	 * Prepare the list-table items for display
	 *
	 * @since 1.0
	 *
	 * @uses $this->_column_headers
	 * @uses $this->items
	 * @uses $this->get_columns()
	 * @uses $this->get_sortable_columns()
	 * @uses $this->get_pagenum()
	 * @uses $this->set_pagination_args()
	 */
	public function prepare_items() {

		// Set column headers
		$this->_column_headers = array(
			$this->get_columns(),
			array(),
			$this->get_sortable_columns()
		);

		// Handle bulk actions
		$this->process_bulk_action();

		// Query parameters
		$per_page     = 20;
		$current_page = $this->get_pagenum();
		$orderby      = ( ! empty( $_REQUEST['orderby'] ) ) ? sanitize_key( $_REQUEST['orderby'] ) : 'meta_id';
		$order        = ( ! empty( $_REQUEST['order']   ) ) ? sanitize_key( $_REQUEST['order']   ) : 'asc';

		// Query for replies
		$meta_data_query  = new WP_Meta_Data_Query( array(
			'number'      => $per_page,
			'paged'       => $current_page,
			'orderby'     => $orderby,
			'order'       => ucwords( $order )
		), $this->object_type );

		// Get the total number of replies, for pagination
		$total_items = $this->get_total_items();

		// Set list table items to queried meta rows
		$this->items = $meta_data_query->metas;
		//var_dump( $this->items );
		// Set the pagination arguments
		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0
	 */
	public function no_items() {
		esc_html_e( 'No meta data found.', 'wp-meta-manager' );
	}

	/**
	 * Display the list table
	 *
	 * This custom method is necessary because the one in `WP_List_Table` comes
	 * with a nonce and check that we do not need.
	 *
	 * @since 1.0
	 */
	public function display() {

		// Top
		$this->display_tablenav( 'top' ); ?>

		<table class="wp-list-table <?php echo implode( ' ', $this->get_table_classes() ); ?>">
			<thead>
				<tr>
					<?php $this->print_column_headers(); ?>
				</tr>
			</thead>

			<tbody id="the-list" data-wp-lists='list:<?php echo $this->_args['singular']; ?>'>
				<?php $this->display_rows_or_placeholder(); ?>
			</tbody>

			<tfoot>
				<tr>
					<?php $this->print_column_headers( false ); ?>
				</tr>
			</tfoot>
		</table>

		<?php

		// Bottom
		$this->display_tablenav( 'bottom' );
	}

	/**
	 * Generate the table navigation above or below the table
	 *
	 * This custom method is necessary because the one in `WP_List_Table` comes
	 * with a nonce and check that we do not need.
	 *
	 * @since 1.0
	 *
	 * @param string $which
	 */
	protected function display_tablenav( $which = '' ) {
		?>

		<div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php
				$this->extra_tablenav( $which );
				$this->pagination( $which );
			?>
			<br class="clear" />
		</div>

		<?php
	}

	public function edit_form( $item ) {
?>
		<div id="wp-meta-edit-<?php echo $item->id; ?>" style="display:none;">
			<h4><?php printf( __( 'Edit Meta ID %d', 'wp-meta-manager' ), $item->id ); ?></h4>
			<form method="post" class="wp-meta-form wp-meta-edit-form">
				<p>
					<label for="wp-meta-edit-meta-key"><?php _e( 'Meta Key', 'wp-meta-manager' ); ?></label>
					<input type="text" name="meta_key" id="wp-meta-edit-meta-key" value="<?php echo esc_attr( $item->meta_key ); ?>"/>	
				</p>
				<p>
					<label for="wp-meta-edit-object-id"><?php _e( 'Object ID', 'wp-meta-manager' ); ?></label>
					<input type="text" name="object_id" id="wp-meta-edit-object-id" value="<?php echo esc_attr( $item->object_id ); ?>"/>	
				</p>
				<p>
					<label for="wp-meta-edit-meta-value"><?php _e( 'Meta Value', 'wp-meta-manager' ); ?></label><br/>
					<textarea name="meta_value" id="wp-meta-edit-meta-value" rows="10"><?php echo esc_textarea( $item->meta_value ); ?></textarea>
				</p>
				<p>
					<?php wp_nonce_field( 'wp-edit-meta-nonce', 'wp-edit-meta-nonce' ); ?>
					<input type="hidden" name="meta_id" value="<?php echo esc_attr( $item->id ); ?>"/>	
					<input type="hidden" name="object_type" value="<?php echo esc_attr( $item->object_type ); ?>"/>	
					<input type="submit" id="wp-meta-edit-meta-submit" class="button-primary" value="<?php _e( 'Update', 'wp-meta-manager' ); ?>"/>	
					<span class="spinner"></span>
				</p>
			</form>
		</div>
<?php
	} 

}

endif;