<?php
/**
 * The orderby functions.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Database
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Database;

use MyThemeShop\Helpers\Arr;

/**
 * OrderBy class.
 */
trait OrderBy {

	/**
	 * Add an order by statement to the current query
	 *
	 *     ->orderBy('created_at')
	 *     ->orderBy('modified_at', 'desc')
	 *
	 *     // multiple order clauses
	 *     ->orderBy(['firstname', 'lastname'], 'desc')
	 *
	 *     // muliple order clauses with diffrent directions
	 *     ->orderBy(['firstname' => 'asc', 'lastname' => 'desc'])
	 *
	 * @param array|string $columns   Columns.
	 * @param string       $direction Direction.
	 *
	 * @return self The current query builder.
	 */
	public function orderBy( $columns, $direction = 'ASC' ) { // @codingStandardsIgnoreLine
		if ( is_string( $columns ) ) {
			$columns = $this->argument_to_array( $columns );
		}

		$direction = $this->sanitize_direction( $direction );

		foreach ( $columns as $key => $column ) {
			if ( is_numeric( $key ) ) {
				$this->add_sql_clause( 'order_by', "{$column}{$direction}" );
				continue;
			}

			$column = $this->sanitize_direction( $column );
			$this->add_sql_clause( 'order_by', "{$key}{$column}" );
		}

		return $this;
	}

	/**
	 * Sanitize direction
	 *
	 * @param string $direction Value to sanitize.
	 *
	 * @return string Sanitized value
	 */
	protected function sanitize_direction( $direction ) {
		if ( empty( $direction ) || 'ASC' === $direction || 'asc' === $direction ) {
			return '';
		}

		return ' ' . \strtoupper( $direction );
	}

	/**
	 * Returns an string argument as parsed array if possible
	 *
	 * @param string $argument Argument to validate.
	 *
	 * @return array
	 */
	protected function argument_to_array( $argument ) {
		if ( false !== strpos( $argument, ',' ) ) {
			return Arr::from_string( $argument );
		}

		return array( $argument );
	}
}
