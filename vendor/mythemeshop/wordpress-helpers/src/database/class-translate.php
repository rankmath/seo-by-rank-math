<?php
/**
 * The translate functions.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Database
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Database;

/**
 * Translate class.
 */
trait Translate {

	/**
	 * Translate the current query to an SQL select statement
	 *
	 * @return string
	 */
	private function translateSelect() { // @codingStandardsIgnoreLine
		$query = array( 'SELECT' );

		if ( $this->found_rows ) {
			$query[] = 'SQL_CALC_FOUND_ROWS';
		}

		if ( $this->distinct ) {
			$query[] = 'DISTINCT';
		}

		$query[] = $this->has_sql_clause( 'select' ) ? $this->get_sql_clause( 'select', true ) : '*';
		$query[] = $this->translateFrom();
		$query[] = $this->get_sql_clause( 'join', true );
		$query[] = $this->get_sql_clause( 'where', true );
		$query[] = $this->translateGroupBy();
		$query[] = $this->translateOrderBy();
		$query[] = $this->translateLimit();

		return join( ' ', array_filter( $query ) );
	}

	/**
	 * Translate the current query to an SQL update statement
	 *
	 * @return string
	 */
	private function translateUpdate() { // @codingStandardsIgnoreLine
		$query = array( "UPDATE {$this->table} SET" );

		// Add the values.
		$values = array();
		foreach ( $this->sql_clauses['values'] as $key => $value ) {
			$values[] = $key . ' = ' . $this->esc_value( $value );
		}

		if ( ! empty( $values ) ) {
			$query[] = join( ', ', $values );
		}

		$query[] = $this->get_sql_clause( 'where', true );
		$query[] = $this->translateLimit();

		return join( ' ', array_filter( $query ) );
	}

	/**
	 * Translate the current query to an SQL delete statement
	 *
	 * @return string
	 */
	private function translateDelete() { // @codingStandardsIgnoreLine
		$query   = array( 'DELETE' );
		$query[] = $this->translateFrom();
		$query[] = $this->get_sql_clause( 'where', true );
		$query[] = $this->translateLimit();

		return join( ' ', array_filter( $query ) );
	}

	/**
	 * Build the from statement.
	 *
	 * @return string
	 */
	private function translateFrom() { // @codingStandardsIgnoreLine
		if ( ! $this->has_sql_clause( 'from' ) ) {
			$this->add_sql_clause( 'from', $this->table );
		}

		return 'FROM ' . $this->get_sql_clause( 'from', true );
	}

	/**
	 * Build the order by statement
	 *
	 * @return string
	 */
	protected function translateOrderBy() { // @codingStandardsIgnoreLine
		if ( ! $this->has_sql_clause( 'order_by' ) ) {
			return '';
		}

		return 'ORDER BY ' . $this->get_sql_clause( 'order_by', true );
	}

	/**
	 * Build the group by clauses.
	 *
	 * @return string
	 */
	private function translateGroupBy() { // @codingStandardsIgnoreLine
		if ( ! $this->has_sql_clause( 'group_by' ) ) {
			return '';
		}

		$group_by = 'GROUP BY ' . $this->get_sql_clause( 'group_by', true );

		if ( $this->has_sql_clause( 'having' ) ) {
			$group_by .= ' ' . $this->get_sql_clause( 'having', true );
		}

		return $group_by;
	}

	/**
	 * Build offset and limit.
	 *
	 * @return string
	 */
	private function translateLimit() { // @codingStandardsIgnoreLine
		return $this->get_sql_clause( 'limit', true );
	}
}
