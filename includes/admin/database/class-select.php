<?php
/**
 * The select functions.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Database
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin\Database;

/**
 * Select class.
 */
trait Select {

	/**
	 * Set the selected fields
	 *
	 * @param array $fields Fields to select.
	 *
	 * @return self The current query builder.
	 */
	public function select( $fields = '' ) {
		if ( empty( $fields ) ) {
			return $this;
		}

		if ( is_string( $fields ) ) {
			$this->add_sql_clause( 'select', $fields );
			return $this;
		}

		foreach ( $fields as $key => $field ) {
			$this->add_sql_clause( 'select', is_string( $key ) ? "$key AS $field" : $field );
		}

		return $this;
	}

	/**
	 * Shortcut to add a count function
	 *
	 *     ->selectCount('id')
	 *     ->selectCount('id', 'count')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query builder.
	 */
	public function selectCount( $field = '*', $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'count', $field, $alias );
	}

	/**
	 * Shortcut to add a sum function
	 *
	 *     ->selectSum('id')
	 *     ->selectSum('id', 'total')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query builder.
	 */
	public function selectSum( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'sum', $field, $alias );
	}

	/**
	 * Shortcut to add a avg function
	 *
	 *     ->selectAvg('id')
	 *     ->selectAvg('id', 'average')
	 *
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query builder.
	 */
	public function selectAvg( $field, $alias = null ) { // @codingStandardsIgnoreLine
		return $this->selectFunc( 'avg', $field, $alias );
	}

	/**
	 * Shortcut to add a function
	 *
	 * @param string $func  Function name.
	 * @param string $field Column name.
	 * @param string $alias (Optional) Column alias.
	 *
	 * @return self The current query builder.
	 */
	public function selectFunc( $func, $field, $alias = null ) { // @codingStandardsIgnoreLine
		$func  = \strtoupper( $func );
		$field = "$func({$field})";
		if ( ! is_null( $alias ) ) {
			$field .= " AS {$alias}";
		}

		$this->add_sql_clause( 'select', $field );

		return $this;
	}

	/**
	 * Distinct select setter
	 *
	 * @param bool $distinct Is distinct.
	 *
	 * @return self The current query builder.
	 */
	public function distinct( $distinct = true ) {
		$this->distinct = $distinct;
		return $this;
	}

	/**
	 * SQL_CALC_FOUND_ROWS select setter
	 *
	 * @param bool $found_rows Should get found rows.
	 *
	 * @return self The current query builder.
	 */
	public function found_rows( $found_rows = true ) {
		$this->found_rows = $found_rows;
		return $this;
	}
}
