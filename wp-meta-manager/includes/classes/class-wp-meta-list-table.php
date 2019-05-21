<?php

/**
 * WP Meta List Table
 *
 * @since 1.0.0
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
 * @since 1.0.0
 */
class WP_Meta_List_table extends WP_List_Table {

	/**
	 * @var string Type of object
	 */
	public $object_type;

	/**
	 * @var string Name of table
	 */
	public $table_name;

	/**
	 * The main constructor method
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @see WP_List_Table::::single_row_columns()
	 *
	 * @return array An associative array containing column information
	 */
	public function get_columns() {
		return array(
			'cb'          => '<input type="checkbox" />',
			'meta_id'     => esc_html__( 'ID',         'wp-meta-manager' ),
			'meta_key'    => esc_html__( 'Meta Key',   'wp-meta-manager' ),
			'object_id'   => esc_html__( 'Object ID',  'wp-meta-manager' ),
			'meta_value'  => esc_html__( 'Meta Value', 'wp-meta-manager' ),
		);
	}

	/**
	 * Define the sortable columns
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
	 *
	 * @return array An associative array containing all the bulk actions
	 */
	public function get_bulk_actions() {
		return array(
			'delete' => esc_html__( 'Delete', 'wp-meta-manager' )
		);
	}

	/**
	 * Get the link used to filter by a certain thing.
	 *
	 * @since 2.0.0
	 * @param string $text
	 * @param array  $args
	 *
	 * @return string
	 */
	private function get_filter_link( $text = '', $args = array() ) {

		// Tab
		$tab = ! empty( $_REQUEST['tab'] )
			? sanitize_key( $_REQUEST['tab'] )
			: 'post';

		// Default args
		$defaults = array(
			'tab' => $tab
		);

		// Key
		if ( ! empty( $_REQUEST['meta_key'] ) ) {
			$defaults['key'] = sanitize_text_field( $_REQUEST['meta_key'] );
		}

		// Object ID
		if ( ! empty( $_REQUEST['object_id'] ) ) {
			$defaults['object_id'] = absint( $_REQUEST['object_id'] );
		}

		// Search
		if ( ! empty( $_REQUEST['s'] ) ) {
			$defaults['s'] = sanitize_text_field( $_REQUEST['s'] );
		}

		// Parse args
		$r = wp_parse_args( $args, $defaults );

		// Get the URL
		$url = add_query_arg( $r, menu_page_url( 'wp-meta-manager', false ) );

		// Return HTML
		return '<a href="' . esc_url( $url ) . '">' . esc_html( $text ) . '</a>';
	}

	/**
	 * Output the check-box column for bulk actions (if we implement them)
	 *
	 * @since 1.0.0
	 */
	public function column_cb( $item = '' ) {
		return sprintf(
			'<input type="checkbox" name="%1$s[]" value="%2$s" />',
			esc_attr( $this->_args['singular'] ),
			absint( $item->id )
		);
	}

	/**
	 * Output the meta_id column
	 *
	 * @since 1.0.0
	 */
	public function column_meta_id( $item = '' ) {

		// meta_id column is "unsigned" so cannot ever be negative
		return absint( $item->id );
	}

	/**
	 * Output the meta_key column
	 *
	 * @since 1.0.0
	 */
	public function column_meta_key( $item = '' ) {

		// Edit
		$edit_url = add_query_arg( array(
			'meta_id'     => $item->id,
			'object_type' => $item->object_type,
			'view'        => 'edit',
			'nonce'       => wp_create_nonce( 'wp-edit-meta' )
		) );

		// Delete
		$delete_url = add_query_arg( array(
			'object_type' => $item->object_type,
			'action'      => 'delete-meta',
			'nonce'       => wp_create_nonce( 'wp-delete-meta' )
		) );

		// Actions
		$actions = array(
			'edit'   => '<a href="' . esc_url( $edit_url   ) . '" class="wp-meta-action-link wp-meta-edit edit"     data-meta-id="' . esc_attr( $item->id ) . '" data-object-type="' . esc_attr( $item->object_type ) . '" data-nonce="' . wp_create_nonce( 'wp-meta-delete' ) . '">' . esc_html__( 'Edit',   'wp-meta-manager' ) . '</a>',
			'delete' => '<a href="' . esc_url( $delete_url ) . '" class="wp-meta-action-link wp-meta-delete delete" data-meta-id="' . esc_attr( $item->id ) . '" data-object-type="' . esc_attr( $item->object_type ) . '" data-nonce="' . wp_create_nonce( 'wp-meta-delete' ) . '">' . esc_html__( 'Delete', 'wp-meta-manager' ) . '</a>'
		);

		// Filter by meta_key
		$link = $this->get_filter_link( $item->meta_key, array(
			'tab'      => $item->object_type,
			'meta_key' => $item->meta_key
		) );

		// Return link and rows
		return $link . '<div class="row-actions">' . $this->row_actions( $actions, true ) . '</div>';
	}

	/**
	 * Output the meta_id column
	 *
	 * @since 1.0.0
	 */
	public function column_object_id( $item = '' ) {
		return $this->get_filter_link( $item->object_id, array(
			'tab'       => $item->object_type,
			'object_id' => $item->object_id
		) );
	}

	/**
	 * Output the meta_value column
	 *
	 * @since 1.0.0
	 */
	public function column_meta_value( $item = '' ) {

		// Empty String
		if ( '' == $item->meta_value ) {
			$retval = esc_html__( 'Empty String', 'wp-meta-manager' );

		// Array
		} elseif ( is_serialized( $item->meta_value ) ) {
			$retval = esc_html__( 'Serialized Array', 'wp-meta-manager' );

		// Some value
		} else {
			$retval = '<code>' . esc_html( $item->meta_value ) . '</code>';
		}

		return $retval;
	}

	/**
	 * Generates content for a single row of the table
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param object $item The current item
	 */
	public function single_row( $item ) {
		echo '<tr id="wp-meta-' . esc_attr( $item->id ) . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	/**
	 * Handle bulk action requests
	 *
	 * @since 1.0.0
	 */
	public function process_bulk_action() {
		switch ( $this->current_action() ) {
			case 'delete' :
				// Handle deletion
				break;
		}
	}

	/**
	 * Prepare the list-table items for display
	 *
	 * @since 1.0.0
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
		$per_page = 20;
		$offset   = $per_page * ( $this->get_pagenum() - 1 );

		// Order by
		$orderby  = ! empty( $_REQUEST['orderby'] )
			? sanitize_key( $_REQUEST['orderby'] )
			: 'meta_id';

		// Order
		$order = ! empty( $_REQUEST['order'] )
			? sanitize_key( $_REQUEST['order'] )
			: 'asc';

		// Search text
		$search = isset( $_GET['s'] )
			? sanitize_text_field( $_GET['s'] )
			: '';

		// Default args
		$args = array(
			'number'  => $per_page,
			'offset'  => $offset,
			'orderby' => $orderby,
			'order'   => strtoupper( $order ),
			'search'  => $search
		);

		// Filter by object_id
		if ( ! empty( $_REQUEST['object_id'] ) ) {
			$args['object_id'] = absint( $_REQUEST['object_id'] );
		}

		// Filter by meta_key
		if ( ! empty( $_REQUEST['meta_key'] ) ) {
			$args['key'] = sanitize_text_field( $_REQUEST['meta_key'] );
		}

		// Query for replies
		$meta_data_query = new WP_Meta_Data_Query( $args, $this->object_type );

		// Set list table items to queried meta rows
		$this->items = $meta_data_query->metas;

		// Set the pagination arguments
		$this->set_pagination_args( array(
			'total_items' => $meta_data_query->found_metas,
			'per_page'    => $per_page,
			'total_pages' => ceil( $meta_data_query->found_metas / $per_page )
		) );
	}

	/**
	 * Message to be displayed when there are no items
	 *
	 * @since 1.0.0
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
	 * @since 1.0.0
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

			<tbody id="the-list" data-wp-lists='list:<?php echo esc_attr( $this->_args['singular'] ); ?>'>
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
	 * @since 1.0.0
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

	/**
	 * Show the search field
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @param string $text Label for the search box
	 * @param string $input_id ID of the search box
	 *
	 * @return svoid
	 */
	public function search_box( $text = '', $input_id = '' ) {

		// Bail if no items to search
		if ( empty( $_REQUEST['s'] ) && ! $this->has_items() ) {
			return;
		}

		// Combine the input ID
		$input_id = sanitize_key( $input_id ) . '-search-input';

		// Hidden orderby field
		if ( ! empty( $_REQUEST['orderby'] ) ) {
			echo '<input type="hidden" name="orderby" value="' . esc_attr( $_REQUEST['orderby'] ) . '" />';
		}

		// Hidden order field
		if ( ! empty( $_REQUEST['order'] ) ) {
			echo '<input type="hidden" name="order" value="' . esc_attr( $_REQUEST['order'] ) . '" />';
		}

		?>

		<p class="search-box">
			<input type="hidden" name="tab" value="<?php echo esc_attr( $this->object_type ); ?>" />
			<label class="screen-reader-text" for="<?php echo esc_attr( $input_id ); ?>"><?php echo esc_html( $text ); ?>:</label>
			<input type="search" id="<?php echo esc_attr( $input_id ); ?>" name="s" value="<?php _admin_search_query(); ?>" />
			<?php submit_button( $text, 'button', false, false, array( 'ID' => 'search-submit' ) ); ?>
		</p>

		<?php
	}

	/**
	 * Edit form HTML
	 *
	 * @access public
	 * @since 1.0.0
	 *
	 * @return svoid
	 */
	public function edit_form( $item ) {
?>
		<div id="wp-meta-edit-<?php echo esc_attr( $item->id ); ?>" style="display:none;">
			<h4><?php printf( esc_html__( 'Edit Meta ID %d', 'wp-meta-manager' ), esc_html( $item->id ) ); ?></h4>
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
