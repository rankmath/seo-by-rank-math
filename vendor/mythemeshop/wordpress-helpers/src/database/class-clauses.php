<?php
/**
 * The clauses functions.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Database
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Database;

/**
 * Clauses class.
 */
trait Clauses {

	/**
	 * List of SQL clauses.
	 *
	 * @var array
	 */
	private $sql_clauses = array();

	/**
	 * SQL clause merge filters.
	 *
	 * @var array
	 */
	private $sql_filters = array(
		'where' => array(
			'where',
			'where_time',
		),
		'join'  => array(
			'right_join',
			'join',
			'left_join',
		),
	);

	/**
	 * Check has SQL clause.
	 *
	 * @param string $type   Clause type.
	 *
	 * @return boolean True if set and not empty.
	 */
	public function has_sql_clause( $type ) {
		return isset( $this->sql_clauses[ $type ] ) && ! empty( $this->sql_clauses[ $type ] );
	}

	/**
	 * Add a SQL clause to be included when get_data is called.
	 *
	 * @param string $type   Clause type.
	 * @param string $clause SQL clause.
	 */
	public function add_sql_clause( $type, $clause ) {
		if ( isset( $this->sql_clauses[ $type ] ) && ! empty( $clause ) ) {
			$this->sql_clauses[ $type ][] = $clause;
		}
	}

	/**
	 * Clear SQL clauses by type.
	 *
	 * @param string|array $types Clause type.
	 */
	protected function clear_sql_clause( $types ) {
		foreach ( (array) $types as $type ) {
			if ( isset( $this->sql_clauses[ $type ] ) ) {
				$this->sql_clauses[ $type ] = array();
			}
		}
	}

	/**
	 * Get SQL clause by type.
	 *
	 * @param string $type     Clause type.
	 * @param string $filtered Whether to filter the return value. Default unfiltered.
	 *
	 * @return string SQL clause.
	 */
	protected function get_sql_clause( $type, $filtered = false ) {
		if ( ! isset( $this->sql_clauses[ $type ] ) ) {
			return '';
		}

		$separator = ' ';
		if ( in_array( $type, array( 'select', 'order_by', 'group_by' ), true ) ) {
			$separator = ', ';
		}

		/**
		 * Default to bypassing filters for clause retrieval internal to data stores.
		 * The filters are applied when the full SQL statement is retrieved.
		 */
		if ( false === $filtered ) {
			return implode( $separator, $this->sql_clauses[ $type ] );
		}

		if ( isset( $this->sql_filters[ $type ] ) ) {
			$clauses = array();
			foreach ( $this->sql_filters[ $type ] as $subset ) {
				$clauses = array_merge( $clauses, $this->sql_clauses[ $subset ] );
			}
		} else {
			$clauses = $this->sql_clauses[ $type ];
		}

		/**
		 * Filter SQL clauses by type and context.
		 *
		 * @param array  $clauses The original arguments for the request.
		 * @param string $context The data store context.
		 */
		$clauses = apply_filters( "rank_math_clauses_{$type}", $clauses, $this->context );
		/**
		 * Filter SQL clauses by type and context.
		 *
		 * @param array  $clauses The original arguments for the request.
		 */
		$clauses = apply_filters( "rank_math_clauses_{$type}_{$this->context}", $clauses );

		return implode( $separator, $clauses );
	}
}
