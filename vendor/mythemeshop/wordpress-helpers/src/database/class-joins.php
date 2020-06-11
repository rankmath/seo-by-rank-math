<?php
/**
 * The joins functions.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Database
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Database;

/**
 * Joins class.
 */
trait Joins {

	/**
	 * Generate left join clause.
	 *
	 * @param string $table    The SQL table.
	 * @param mixed  $column1  The SQL Column.
	 * @param mixed  $column2  The SQL Column.
	 * @param string $operator The Operator.
	 * @param string $alias    The table alias.
	 *
	 * @return self The current query builder.
	 */
	public function leftJoin( $table, $column1, $column2, $operator = '=', $alias = '' ) { // @codingStandardsIgnoreLine
		if ( empty( $table ) || empty( $column1 ) || empty( $column2 ) ) {
			return $this;
		}

		if ( ! empty( $alias ) ) {
			$table = "{$table} AS {$alias}";
		}

		$this->add_sql_clause( 'left_join', "LEFT JOIN {$table} ON {$column1} {$operator} {$column2}" );

		return $this;
	}

	/**
	 * Generate right join clause.
	 *
	 * @param string $table    The SQL table.
	 * @param mixed  $column1  The SQL Column.
	 * @param mixed  $column2  The SQL Column.
	 * @param string $operator The Operator.
	 * @param string $alias    The table alias.
	 *
	 * @return self The current query builder.
	 */
	public function rightJoin( $table, $column1, $column2, $operator = '=', $alias = '' ) { // @codingStandardsIgnoreLine
		if ( empty( $table ) || empty( $column1 ) || empty( $column2 ) ) {
			return $this;
		}

		if ( ! empty( $alias ) ) {
			$table = "{$table} AS {$alias}";
		}

		$this->add_sql_clause( 'right_join', "RIGHT JOIN {$table} ON {$column1} {$operator} {$column2}" );

		return $this;
	}

	/**
	 * Generate left join clause.
	 *
	 * @param string $table    The SQL table.
	 * @param mixed  $column1  The SQL Column.
	 * @param mixed  $column2  The SQL Column.
	 * @param string $operator The Operator.
	 * @param string $alias    The table alias.
	 *
	 * @return self The current query builder.
	 */
	public function join( $table, $column1, $column2, $operator = '=', $alias = '' ) { // @codingStandardsIgnoreLine
		if ( empty( $table ) || empty( $column1 ) || empty( $column2 ) ) {
			return $this;
		}

		if ( ! empty( $alias ) ) {
			$table = "{$table} AS {$alias}";
		}

		$this->add_sql_clause( 'join', "JOIN {$table} ON {$column1} {$operator} {$column2}" );

		return $this;
	}
}
