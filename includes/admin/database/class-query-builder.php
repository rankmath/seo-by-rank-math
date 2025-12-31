<?php
/**
 * The Query Builder.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Database
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin\Database;

/**
 * Query_Builder class.
 */
class Query_Builder {

	use Escape;
	use Select;
	use Where;
	use Joins;
	use GroupBy;
	use OrderBy;
	use Clauses;
	use Translate;

	/**
	 * Table name.
	 *
	 * @var string
	 */
	public $table = '';

	/**
	 * Save last query.
	 *
	 * @var string
	 */
	public $last_query = '';

	/**
	 * Make a distinct selection
	 *
	 * @var bool
	 */
	protected $distinct = false;

	/**
	 * Make SQL_CALC_FOUND_ROWS in selection
	 *
	 * @var bool
	 */
	protected $found_rows = false;

	/**
	 * Data store context used to pass to filters.
	 *
	 * @var string
	 */
	protected $context;

	/**
	 * Constructor
	 *
	 * @param string $table   The table name.
	 * @param string $context Optional context passed to filters. Default empty string.
	 */
	public function __construct( $table = '', $context = '' ) {
		$this->table   = $table;
		$this->context = $context;
		$this->reset();
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return mixed
	 */
	public function get( $output = \OBJECT ) {
		return $this->get_results( $this->translateSelect(), $output );
	}

	/**
	 * Translate the given query object and return the results
	 *
	 * @param string $output (Optional) Any of ARRAY_A | ARRAY_N | OBJECT | OBJECT_K constants.
	 *
	 * @return mixed
	 */
	public function one( $output = \OBJECT ) {
		return $this->get_row( $this->translateSelect(), $output );
	}

	/**
	 * Translate the given query object and return one variable from the database
	 *
	 * @return mixed
	 */
	public function getVar() { // @codingStandardsIgnoreLine
		$row = $this->one( \ARRAY_A );

		return is_null( $row ) ? false : current( $row );
	}

	/**
	 * Insert a row into a table
	 *
	 * @codeCoverageIgnore
	 * @see wpdb::insert()
	 *
	 * @param array $data   Data to insert (in column => value pairs). Both $data columns and $data values should be "raw" (neither should be SQL escaped).
	 * @param array $format (Optional) An array of formats to be mapped to each of the value in $data.
	 *
	 * @return mixed
	 */
	public function insert( $data, $format = null ) {
		global $wpdb;

		$wpdb->insert( $this->table, $data, $format );

		return $wpdb->insert_id;
	}

	/**
	 * Update a row into a table
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function update() {

		$query = $this->translateUpdate();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Delete data from table
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function delete() {

		$query = $this->translateDelete();
		$this->reset();

		return $this->query( $query );
	}

	/**
	 * Truncate table.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return mixed
	 */
	public function truncate() {
		return $this->query( "truncate table {$this->table};" );
	}

	/**
	 * Get found rows.
	 *
	 * @return int
	 */
	public function get_found_rows() {
		return $this->get_var( 'SELECT FOUND_ROWS();' );
	}

	/**
	 * Get a single variable from the database.
	 *
	 * @param string $query The SQL query to run.
	 */
	public function get_var( $query ) {
		return $this->run(
			[
				'type'  => 'var',
				'query' => $query,
			]
		);
	}

	/**
	 * Perform a MySQL database query, using current database connection.
	 *
	 * @codeCoverageIgnore
	 *
	 * @see wpdb::query
	 *
	 * @param string $query Database query.
	 *
	 * @return int|false Number of rows affected|selected or false on error.
	 */
	public function query( $query ) {
		return $this->run(
			[
				'type'  => 'query',
				'query' => $query,
			]
		);
	}

	/**
	 * Set the limit clause.
	 *
	 * @param int $limit  Limit size.
	 * @param int $offset Offeset.
	 *
	 * @return self The current query builder.
	 */
	public function limit( $limit, $offset = 0 ) {
		global $wpdb;
		$limit  = \absint( $limit );
		$offset = \absint( $offset );

		$this->clear_sql_clause( 'limit' );
		$this->add_sql_clause( 'limit', $wpdb->prepare( 'LIMIT %d, %d', $offset, $limit ) );

		return $this;
	}

	/**
	 * Create an query limit based on a page and a page size
	 *
	 * @param int $page Page number.
	 * @param int $size Page size.
	 *
	 * @return self The current query builder.
	 */
	public function page( $page, $size = 25 ) {
		$size   = \absint( $size );
		$offset = $size * \absint( $page );

		$this->limit( $size, $offset );

		return $this;
	}

	/**
	 * Set values for insert/update
	 *
	 * @param string|array $name  Key of pair.
	 * @param string|array $value Value of pair.
	 *
	 * @return self The current query builder.
	 */
	public function set( $name, $value = null ) {
		if ( is_array( $name ) ) {
			$this->sql_clauses['values'] = $this->sql_clauses['values'] + $name;

			return $this;
		}

		$this->sql_clauses['values'][ $name ] = $value;

		return $this;
	}

	/**
	 * Reset all variables.
	 *
	 * @return self The current query builder.
	 */
	private function reset() {
		$this->distinct    = false;
		$this->found_rows  = false;
		$this->sql_clauses = [
			'select'     => [],
			'from'       => [],
			'left_join'  => [],
			'join'       => [],
			'right_join' => [],
			'where'      => [],
			'where_time' => [],
			'group_by'   => [],
			'having'     => [],
			'limit'      => [],
			'order_by'   => [],
			'values'     => [],
		];

		return $this;
	}

	/**
	 * Get a single column from the database.
	 *
	 * @param string $query The SQL query to run.
	 * @param int    $index The column index to retrieve.
	 */
	public function get_col( $query = '', $index = 0 ) {
		return $this->run(
			[
				'type'  => 'col',
				'query' => $query,
				'index' => $index,
			]
		);
	}

	/**
	 * Get a single row from the database.
	 *
	 * @param string $query  The SQL query to run.
	 * @param string $output The output to retrieve.
	 * @param int    $index  The row index to retrieve.
	 */
	public function get_row( $query = '', $output = OBJECT, $index = 0 ) {
		return $this->run(
			[
				'type'   => 'row',
				'query'  => $query,
				'index'  => $index,
				'output' => $output,
			]
		);
	}

	/**
	 * Get results from the database.
	 *
	 * @param string $query  The SQL query to run.
	 * @param string $output The output format.
	 */
	public function get_results( $query = '', $output = OBJECT ) {
		return $this->run(
			[
				'query'  => $query,
				'output' => $output,
			]
		);
	}

	/**
	 * Run the query and return the results.
	 *
	 * @param array $args The query arguments.
	 */
	public function run( $args = [] ) {
		global $wpdb;

		$output = isset( $args['output'] ) && $args['output'] ? $args['output'] : OBJECT;
		$type   = $args['type'] ?? '';
		$query  = $args['query'] ?? '';
		$index  = $args['index'] ?? '';

		$start_time = microtime( true );

		$this->last_query = $query;
		$this->reset();

		switch ( $type ) {
			case 'row':
				$results = $wpdb->get_row( $query, $output, $index );
				break;
			case 'col':
				$results = $wpdb->get_col( $query, $index );
				break;
			case 'query':
				$results = $wpdb->query( $query );
				break;
			case 'var':
				$results = $wpdb->get_var( $query );
				break;
			default:
				$results = $wpdb->get_results( $query, $output );
				break;
		}

		return apply_filters( 'rank_math/database/query/results', $results, $args, $start_time );
	}
}
