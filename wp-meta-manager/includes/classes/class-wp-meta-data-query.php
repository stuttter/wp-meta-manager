<?php
/**
 * WP_Meta_Data_Query class
 *
 * @package Plugins/MetaQueries
 * @since 1.0.0
 */

/**
 * Core class used for querying metadata tables.
 *
 * @since 1.0.0
 *
 * @see WP_Meta_Data_Query::__construct() for accepted arguments.
 */
class WP_Meta_Data_Query {

	/**
	 * SQL for database query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var string
	 */
	public $request;

	/**
	 * WP_Meta_Type object
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var string
	 */
	protected $meta_object;

	/**
	 * SQL query clauses.
	 *
	 * @since 1.0.0
	 * @access protected
	 * @var array
	 */
	protected $sql_clauses = array(
		'select'  => '',
		'from'    => '',
		'where'   => array(),
		'groupby' => '',
		'orderby' => '',
		'limits'  => '',
	);

	/**
	 * Query vars set by the user.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $query_vars;

	/**
	 * Default values for query vars.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $query_var_defaults;

	/**
	 * List of metas located by the query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var array
	 */
	public $metas;

	/**
	 * The amount of found metas for the current query.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int
	 */
	public $found_metas = 0;

	/**
	 * The number of pages.
	 *
	 * @since 1.0.0
	 * @access public
	 * @var int
	 */
	public $max_num_pages = 0;

	/**
	 * The database object
	 *
	 * @since 1.0.0
	 *
	 * @var WPDB
	 */
	private $db;

	/**
	 * Sets up the meta query, based on the query vars passed.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query {
	 *     Optional. Array or query string of meta query parameters. Default empty.
	 *
	 *     @type int          $meta_id           An meta ID to only return that meta. Default empty.
	 *     @type array        $meta_id__in       Array of meta IDs to include. Default empty.
	 *     @type array        $meta_id__not_in   Array of meta IDs to exclude. Default empty.
	 *     @type int          $object_id         An object ID to only return that object. Default empty.
	 *     @type array        $object_id__in     Array of object IDs to include. Default empty.
	 *     @type array        $object_id__not_in Array of object IDs to exclude. Default empty.
	 *     @type string       $key               Limit results to those affiliated with a given key.
	 *                                           Default empty.
	 *     @type array        $key__in           Array of types to include affiliated keys for. Default empty.
	 *     @type array        $key__not_in       Array of types to exclude affiliated keys for. Default empty.
	 *     @type string       $value             Limit results to those affiliated with a given value.
	 *                                           Default empty.
	 *     @type array        $value__in         Array of types to include affiliated value for. Default empty.
	 *     @type array        $value__not_in     Array of types to exclude affiliated value for. Default empty.
	 *     @type bool         $count             Whether to return a meta count (true) or array of meta objects.
	 *                                           Default false.
	 *     @type string       $fields            Site fields to return. Accepts 'ids' (returns an array of meta IDs)
	 *                                           or empty (returns an array of complete meta objects). Default empty.
	 *     @type int          $number            Maximum number of metas to retrieve. Default null (no limit).
	 *     @type int          $offset            Number of metas to offset the query. Used to build LIMIT clause.
	 *                                           Default 0.
	 *     @type bool         $no_found_rows     Whether to disable the `SQL_CALC_FOUND_ROWS` query. Default true.
	 *     @type string|array $orderby           Site status or array of statuses. Accepts 'meta_id', 'object_id', 'key', 'value'
	 *                                           Also accepts false, an empty array, or 'none' to disable `ORDER BY` clause.
	 *                                           Default 'meta_id'.
	 *     @type string       $order             How to order retrieved metas. Accepts 'ASC', 'DESC'. Default 'ASC'.
	 *     @type string       $search            Search term(s) to retrieve matching metas for. Default empty.
	 *     @type array        $search_columns    Array of column names to be searched. Accepts 'domain', 'status', 'type'.
	 *                                           Default empty array.
	 *
	 *     @type bool         $update_meta_cache Whether to prime the cache for found metas. Default false.
	 * }
	 */
	public function __construct( $query = '', $type = '' ) {

		// Bail if no meta object
		$this->meta_object = wp_get_meta_type( $type );
		if ( empty( $this->meta_object ) ) {
			return;
		}

		$this->query_var_defaults = array(
			'fields'            => '',
			'meta_id'           => '',
			'meta_id__in'       => '',
			'meta_id__not_in'   => '',
			'object_id'         => '',
			'object_id__in'     => '',
			'object_id__not_in' => '',
			'key'               => '',
			'key__in'           => '',
			'key__not_in'       => '',
			'value'             => '',
			'value__in'         => '',
			'value__not_in'     => '',
			'number'            => 100,
			'offset'            => '',
			'orderby'           => 'meta_id',
			'order'             => 'ASC',
			'search'            => '',
			'search_columns'    => array(),
			'count'             => false,
			'no_found_rows'     => false,
			'update_meta_cache' => true,
		);

		// Only query if a query is passed. Allows object creation without
		// forcing a query.
		if ( ! empty( $query ) ) {
			$this->query( $query );
		}
	}

	/**
	 * Parses arguments passed to the meta query with default query parameters.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @see WP_Meta_Data_Query::__construct()
	 *
	 * @param string|array $query Array or string of WP_Meta_Data_Query arguments. See WP_Meta_Data_Query::__construct().
	 */
	public function parse_query( $query = '' ) {
		if ( empty( $query ) ) {
			$query = $this->query_vars;
		}

		$this->query_vars = wp_parse_args( $query, $this->query_var_defaults );

		/**
		 * Fires after the meta query vars have been parsed.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Meta_Data_Query &$this The WP_Meta_Data_Query instance (passed by reference).
		 */
		do_action_ref_array( 'parse_meta_query', array( &$this ) );
	}

	/**
	 * Sets up the WordPress query for retrieving metas.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @param string|array $query Array or URL query string of parameters.
	 * @return array|int List of metas, or number of metas when 'count' is passed as a query var.
	 */
	public function query( $query ) {
		$this->query_vars = wp_parse_args( $query );

		return $this->get_metas();
	}

	/**
	 * Retrieves a list of metas matching the query vars.
	 *
	 * @since 1.0.0
	 * @access public
	 *
	 * @return array|int List of metas, or number of metas when 'count' is passed as a query var.
	 */
	public function get_metas() {
		$this->parse_query();

		/**
		 * Fires before metas are retrieved.
		 *
		 * @since 1.0.0
		 *
		 * @param WP_Meta_Data_Query &$this Current instance of WP_Meta_Data_Query, passed by reference.
		 */
		do_action_ref_array( 'pre_get_metas', array( &$this ) );

		// $args can include anything. Only use the args defined in the query_var_defaults to compute the key.
		$key          = md5( serialize( wp_array_slice_assoc( $this->query_vars, array_keys( $this->query_var_defaults ) ) ) );
		$cache_group  = "{$this->meta_object->object_type}_meta_manager";

		// Look for cached query
		$last_changed = wp_cache_get_last_changed( $cache_group );
		$cache_key    = "get_metas:{$key}:{$last_changed}";
		$cache_value  = wp_cache_get( $cache_key, $cache_group );

		if ( false === $cache_value ) {
			$meta_ids = $this->get_meta_ids();

			if ( ! empty( $meta_ids ) ) {
				$this->set_found_metas( $meta_ids );
			}

			wp_cache_add( $cache_key, array(
				'meta_ids'    => $meta_ids,
				'found_metas' => $this->found_metas,
			), $cache_group );

		} else {
			$meta_ids          = $cache_value['meta_ids'];
			$this->found_metas = $cache_value['found_metas'];
		}

		if ( $this->found_metas && $this->query_vars['number'] ) {
			$this->max_num_pages = ceil( $this->found_metas / $this->query_vars['number'] );
		}

		// If querying for a count only, there's nothing more to do.
		if ( $this->query_vars['count'] ) {
			// $meta_ids is actually a count in this case.
			return absint( $meta_ids );
		}

		// Unsigned ints cannot be negative
		$meta_ids = array_map( 'absint', $meta_ids );

		// Only IDs
		if ( 'ids' === $this->query_vars['fields'] ) {
			$this->metas = $meta_ids;

			return $this->metas;
		}

		// Prime meta caches.
		if ( $this->query_vars['update_meta_cache'] ) {
			//_prime_meta_caches( $meta_ids );
		}

		// Fetch full meta objects from the primed cache.
		$_metas = array();
		foreach ( $meta_ids as $meta_id ) {
			$_meta = get_meta( $this->meta_object->object_type, $meta_id );
			if ( ! empty( $_meta ) ) {
				$_metas[] = $_meta;
			}
		}

		/**
		 * Filters the meta query results.
		 *
		 * @since 1.0.0
		 *
		 * @param array              $results An array of metas.
		 * @param WP_Meta_Data_Query &$this   Current instance of WP_Meta_Data_Query, passed by reference.
		 */
		$_metas = apply_filters_ref_array( 'the_metas', array( $_metas, &$this ) );

		// Convert to WP_Meta instances.
		$this->metas = $_metas;

		return $this->metas;
	}

	/**
	 * Used internally to get a list of meta IDs matching the query vars.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @return int|array A single count of meta IDs if a count query. An array of meta IDs if a full query.
	 */
	protected function get_meta_ids() {
		$order = $this->parse_order( $this->query_vars['order'] );

		// Disable ORDER BY with 'none', an empty array, or boolean false.
		if ( in_array( $this->query_vars['orderby'], array( 'none', array(), false ), true ) ) {
			$orderby = '';
		} elseif ( ! empty( $this->query_vars['orderby'] ) ) {
			$ordersby = is_array( $this->query_vars['orderby'] ) ?
				$this->query_vars['orderby'] :
				preg_split( '/[,\s]/', $this->query_vars['orderby'] );

			$orderby_array = array();

			foreach ( $ordersby as $_key => $_value ) {

				// Skip if empty
				if ( empty( $_value ) ) {
					continue;
				}

				// Int key
				if ( is_int( $_key ) ) {
					$_orderby = $_value;
					$_order   = $order;

				// Non-int key
				} else {
					$_orderby = $_key;
					$_order   = $_value;
				}

				$parsed = $this->parse_orderby( $_orderby );

				// Skip if empty after parsing
				if ( empty( $parsed ) ) {
					continue;
				}

				// "__in" orderby's
				if ( 'meta_id__in' === $_orderby || 'object_id__in' === $_orderby || 'key__in' === $_orderby || 'value__in' === $_orderby ) {
					$orderby_array[] = $parsed;
					continue;
				}

				$orderby_array[] = $parsed . ' ' . $this->parse_order( $_order );
			}

			$orderby = implode( ', ', $orderby_array );
		} else {
			$orderby = "{$this->meta_object->columns['meta_id']} {$order}";
		}

		// Cast to int
		$number = absint( $this->query_vars['number'] );
		$offset = absint( $this->query_vars['offset'] );

		// LIMITs
		if ( ! empty( $number ) ) {
			$limits = ! empty( $offset )
				? "LIMIT {$offset}, {$number}"
				: "LIMIT {$number}";
		}

		// Fields (maybe count)
		$fields = ! empty( $this->query_vars['count'] )
			? 'COUNT(*)'
			: $this->meta_object->columns['meta_id'];

		/** meta_id ***********************************************************/

		// Parse meta IDs for an IN clause.
		$meta_id = absint( $this->query_vars['meta_id'] );
		if ( ! empty( $meta_id ) ) {
			$this->sql_clauses['where']['meta_id'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_id']} = %d", $meta_id );
		}

		// Parse meta IDs for an IN clause.
		if ( ! empty( $this->query_vars['meta_id__in'] ) ) {
			if ( 1 === count( $this->query_vars['in'] ) ) {
				$this->sql_clauses['where']['meta_id'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_id']} = %d", reset( $this->query_vars['in'] ) );
			} else {
				$this->sql_clauses['where']['meta_id__in'] = "{$this->meta_object->columns['meta_id']} IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['meta_id__in'] ) ) . ' )';
			}
		}

		// Parse meta IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['meta_id__not_in'] ) ) {
			$this->sql_clauses['where']['meta_id__not_in'] = "{$this->meta_object->columns['meta_id']} NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['meta_id__not_in'] ) ) . ' )';
		}

		/** object_id *********************************************************/

		// Parse object IDs for an IN clause.
		$object_id = absint( $this->query_vars['object_id'] );
		if ( ! empty( $object_id ) ) {
			$this->sql_clauses['where']['object_id'] = $this->get_db()->prepare( "{$this->meta_object->columns['object_id']} = %d", $object_id );
		}

		// Parse object IDs for an IN clause.
		if ( ! empty( $this->query_vars['object_id__in'] ) ) {
			if ( 1 === count( $this->query_vars['in'] ) ) {
				$this->sql_clauses['where']['object_id'] = $this->get_db()->prepare( "{$this->meta_object->columns['object_id']} = %d", reset( $this->query_vars['in'] ) );
			} else {
				$this->sql_clauses['where']['object_id__in'] = "{$this->meta_object->columns['object_id']} IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['object_id__in'] ) ) . ' )';
			}
		}

		// Parse object IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['object_id__not_in'] ) ) {
			$this->sql_clauses['where']['object_id__not_in'] = "{$this->meta_object->columns['object_id']} NOT IN ( " . implode( ',', wp_parse_id_list( $this->query_vars['object_id__not_in'] ) ) . ' )';
		}

		/** key ***************************************************************/

		// Parse object IDs for an IN clause.
		$key = $this->query_vars['key'];
		if ( ! empty( $key ) ) {
			$this->sql_clauses['where']['key'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_key']} = %s", $key );
		}

		// Parse object IDs for an IN clause.
		if ( ! empty( $this->query_vars['key__in'] ) ) {
			if ( 1 === count( $this->query_vars['in'] ) ) {
				$this->sql_clauses['where']['key'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_key']} = %s", reset( $this->query_vars['in'] ) );
			} else {
				$this->sql_clauses['where']['key__in'] = "{$this->meta_object->columns['meta_key']} IN ( " . implode( ',', $this->query_vars['key__in'] ) . ' )';
			}
		}

		// Parse object IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['key__not_in'] ) ) {
			$this->sql_clauses['where']['key__not_in'] = "{$this->meta_object->columns['meta_key']} NOT IN ( " . implode( ',', $this->query_vars['key__not_in'] ) . ' )';
		}

		/** value *************************************************************/

		// Parse object IDs for an IN clause.
		$value = $this->query_vars['value'];
		if ( ! empty( $value ) ) {
			$this->sql_clauses['where']['value'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_value']} = %d", $value );
		}

		// Parse object IDs for an IN clause.
		if ( ! empty( $this->query_vars['value__in'] ) ) {
			if ( 1 === count( $this->query_vars['in'] ) ) {
				$this->sql_clauses['where']['value'] = $this->get_db()->prepare( "{$this->meta_object->columns['meta_value']} = %s", reset( $this->query_vars['in'] ) );
			} else {
				$this->sql_clauses['where']['value__in'] = "{$this->meta_object->columns['meta_value']} IN ( " . implode( ',', $this->query_vars['value__in'] ) . ' )';
			}
		}

		// Parse object IDs for a NOT IN clause.
		if ( ! empty( $this->query_vars['value__not_in'] ) ) {
			$this->sql_clauses['where']['value__not_in'] = "{$this->meta_object->columns['meta_value']} NOT IN ( " . implode( ',', $this->query_vars['value__not_in'] ) . ' )';
		}

		/** Search ************************************************************/

		// Falsey search strings are ignored.
		if ( strlen( $this->query_vars['search'] ) ) {
			$search_columns = array();

			// Search columns
			if ( $this->query_vars['search_columns'] ) {
				$search_columns = array_intersect( $this->query_vars['search_columns'], array_values( $this->meta_object->columns ) );
			}

			// Default columns
			if ( empty( $search_columns ) ) {
				$search_columns = array_values( $this->meta_object->columns );
			}

			/**
			 * Filters the columns to search in a WP_Meta_Data_Query search.
			 *
			 * The default columns include 'domain' and 'path.
			 *
			 * @since 1.0.0
			 *
			 * @param array              $search_columns Array of column names to be searched.
			 * @param string             $search         Text being searched.
			 * @param WP_Meta_Data_Query $this           The current WP_Meta_Data_Query instance.
			 */
			$search_columns = apply_filters( 'meta_search_columns', $search_columns, $this->query_vars['search'], $this );

			$this->sql_clauses['where']['search'] = $this->get_search_sql( $this->query_vars['search'], $search_columns );
		}

		$where = implode( ' AND ', $this->sql_clauses['where'] );

		// Not used, but defined so filters can
		$groupby = '';

		// Define the query clause filters
		$pieces = array( 'fields', 'where', 'orderby', 'limits', 'groupby' );

		/**
		 * Filters the meta query clauses.
		 *
		 * @since 1.0.0
		 *
		 * @param array $pieces A compacted array of meta query clauses.
		 * @param WP_Meta_Data_Query &$this Current instance of WP_Meta_Data_Query, passed by reference.
		 */
		$clauses = apply_filters_ref_array( 'meta_clauses', array( compact( $pieces ), &$this ) );

		$fields  = isset( $clauses['fields']  ) ? $clauses['fields']  : '';
		$where   = isset( $clauses['where']   ) ? $clauses['where']   : '';
		$orderby = isset( $clauses['orderby'] ) ? $clauses['orderby'] : '';
		$limits  = isset( $clauses['limits']  ) ? $clauses['limits']  : '';
		$groupby = isset( $clauses['groupby'] ) ? $clauses['groupby'] : '';

		if ( $where ) {
			$where = "WHERE {$where}";
		}

		if ( $groupby ) {
			$groupby = "GROUP BY {$groupby}";
		}

		if ( $orderby ) {
			$orderby = "ORDER BY {$orderby}";
		}

		$found_rows = '';
		if ( ! $this->query_vars['no_found_rows'] ) {
			$found_rows = 'SQL_CALC_FOUND_ROWS';
		}

		$this->sql_clauses['select']  = "SELECT {$found_rows} {$fields}";
		$this->sql_clauses['from']    = "FROM {$this->meta_object->table_name}";
		$this->sql_clauses['groupby'] = $groupby;
		$this->sql_clauses['orderby'] = $orderby;
		$this->sql_clauses['limits']  = $limits;

		$this->request = "{$this->sql_clauses['select']} {$this->sql_clauses['from']} {$where} {$this->sql_clauses['groupby']} {$this->sql_clauses['orderby']} {$this->sql_clauses['limits']}";

		// Do the COUNT query
		if ( ! empty( $this->query_vars['count'] ) ) {
			return absint( $this->get_db()->get_var( $this->request ) );
		}

		// Do the column query
		$meta_ids = $this->get_db()->get_col( $this->request );

		// Return all IDs as absolute ints
		return array_map( 'absint', $meta_ids );
	}

	/**
	 * Populates found_metas and max_num_pages properties for the current query
	 * if the limit clause was used.
	 *
	 * @since 1.0.0
	 * @access private
	 *
	 * @param  array $meta_ids Optional array of meta IDs
	 */
	private function set_found_metas( $meta_ids = array() ) {

		// Items were not found
		if ( empty( $meta_ids ) ) {
			return;
		}

		// Default to number of item IDs
		$this->found_metas = count( (array) $meta_ids );

		// Count query
		if ( ! empty( $this->query_vars['count'] ) ) {

			// Not grouped
			if ( is_numeric( $meta_ids ) && empty( $this->query_vars['groupby'] ) ) {
				$this->found_metas = intval( $meta_ids );
			}

		// Not a count query
		} elseif ( is_array( $meta_ids ) && ( ! empty( $this->query_vars['number'] ) && empty( $this->query_vars['no_found_rows'] ) ) ) {

			/**
			 * Filters the query used to retrieve found meta count.
			 *
			 * @since 1.0.0
			 *
			 * @param string             $found_metas_query SQL query. Default 'SELECT FOUND_ROWS()'.
			 * @param WP_Meta_Data_Query $meta_query        The `WP_Meta_Data_Query` instance.
			 */
			$found_metas_query = apply_filters( 'found_metas_query', 'SELECT FOUND_ROWS()', $this );

			// Maybe query for found metas
			if ( ! empty( $found_metas_query ) ) {
				$this->found_metas = (int) $this->get_db()->get_var( $found_metas_query );
			}
		}
	}

	/**
	 * Used internally to generate an SQL string for searching across multiple columns.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $string  Search string.
	 * @param array  $columns Columns to search.
	 * @return string Search SQL.
	 */
	protected function get_search_sql( $string, $columns ) {

		if ( false !== strpos( $string, '*' ) ) {
			$like = '%' . implode( '%', array_map( array( $this->get_db(), 'esc_like' ), explode( '*', $string ) ) ) . '%';
		} else {
			$like = '%' . $this->get_db()->esc_like( $string ) . '%';
		}

		$searches = array();
		foreach ( $columns as $column ) {
			$searches[] = $this->get_db()->prepare( "$column LIKE %s", $like );
		}

		return '(' . implode( ' OR ', $searches ) . ')';
	}

	/**
	 * Parses and sanitizes 'orderby' keys passed to the meta query.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $orderby Meta for the field to order by.
	 * @return string|false Value to used in the ORDER clause. False otherwise.
	 */
	protected function parse_orderby( $orderby ) {

		$parsed = false;

		switch ( $orderby ) {
			case 'id':
			case 'meta_id':
				$parsed = "{$this->meta_object->columns['meta_id']}";
				break;
			case 'object_id':
				$parsed = "{$this->meta_object->columns['object_id']}";
				break;
			case 'key':
			case 'meta_key':
				$parsed = "{$this->meta_object->columns['meta_key']}";
				break;
			case 'value':
			case 'meta_value':
				$parsed = "{$this->meta_object->columns['meta_value']}";
				break;
			case 'meta_id__in':
				$meta__in = implode( ',', array_map( 'absint', $this->query_vars['meta_id__in'] ) );
				$parsed   = "FIELD( {$this->type->column_name}, $meta__in )";
				break;
			case 'object_id__in':
				$meta__in = implode( ',', array_map( 'absint', $this->query_vars['object_id__in'] ) );
				$parsed   = "FIELD( {$this->type->column_name}, $meta__in )";
				break;
			case 'key__in':
				$meta__in = implode( ',', array_map( 'wp_unslash', $this->query_vars['key__in'] ) );
				$parsed   = "FIELD( meta_key, $meta__in )";
				break;
			case 'value__in':
				$meta__in = implode( ',', array_map( 'wp_unslash', $this->query_vars['value__in'] ) );
				$parsed   = "FIELD( meta_value, $meta__in )";
				break;
		}

		return $parsed;
	}

	/**
	 * Parses an 'order' query variable and cast it to 'ASC' or 'DESC' as necessary.
	 *
	 * @since 1.0.0
	 * @access protected
	 *
	 * @param string $order The 'order' query variable.
	 * @return string The sanitized 'order' query variable.
	 */
	protected function parse_order( $order ) {
		if ( ! is_string( $order ) || empty( $order ) ) {
			return 'ASC';
		}

		if ( 'ASC' === strtoupper( $order ) ) {
			return 'ASC';
		} else {
			return 'DESC';
		}
	}

	/**
	 * Return the database interface.
	 *
	 * @since 2.0.0
	 *
	 * @return object
	 */
	private function get_db() {
		return $GLOBALS['wpdb'];
	}
}
