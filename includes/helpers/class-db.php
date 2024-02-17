<?php
/**
 * DB helpers.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Admin\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * DB class.
 */
class DB {

	/**
	 * Check and fix collation of table and columns.
	 *
	 * @param string $table         Table name (without prefix).
	 * @param array  $columns       Columns.
	 * @param string $set_collation Collation.
	 */
	public static function check_collation( $table, $columns = 'all', $set_collation = null ) {
		global $wpdb;
		$changed_collations = 0;

		$prefixed = $wpdb->prefix . $table;

		$sql = "SHOW TABLES LIKE '{$wpdb->prefix}%'";
		$res = $wpdb->get_col( $sql ); // phpcs:ignore
		if ( ! in_array( $prefixed, $res, true ) ) {
			return $changed_collations;
		}

		// Collation to set.
		$collate = $set_collation ? $set_collation : self::get_default_collation();

		$sql = "SHOW CREATE TABLE `{$prefixed}`";
		$res = $wpdb->get_row( $sql ); // phpcs:ignore

		$table_collate = $res->{'Create Table'};

		// Determine current collation value.
		$current_collate = '';
		if ( preg_match( '/COLLATE=([a-zA-Z0-9_-]+)/', $table_collate, $matches ) ) {
			$current_collate = $matches[1];
		}

		// If collation is not set or is incorrect, fix it.
		if ( ! $current_collate || $current_collate !== $collate ) {
			$sql = "ALTER TABLE `{$prefixed}` COLLATE={$collate}";
			error_log( sprintf( 'Rank Math: Changing collation of `%1$s` table from %2$s to %3$s. SQL: "%4$s"', $prefixed, $current_collate, $collate, $sql ) ); // phpcs:ignore
			$wpdb->query( $sql ); // phpcs:ignore
			$changed_collations++;
		}

		// Now handle columns if needed.
		if ( ! $columns ) {
			return $changed_collations;
		}

		$sql = "SHOW FULL COLUMNS FROM {$prefixed}";
		$res = $wpdb->get_results( $sql, ARRAY_A ); // phpcs:ignore
		if ( ! $res ) {
			return $changed_collations;
		}

		$columns = 'all' === $columns ? wp_list_pluck( $res, 'Field' ) : $columns;

		foreach ( $res as $col ) {
			if ( ! in_array( $col['Field'], $columns, true ) ) {
				continue;
			}

			$current_collate = $col['Collation'];
			if ( ! $current_collate || $current_collate === $collate ) {
				continue;
			}

			$null    = 'NO' === $col['Null'] ? 'NOT NULL' : 'NULL';
			$default = ! empty( $col['Default'] ) ? "DEFAULT '{$col['Default']}'" : '';

			$sql = "ALTER TABLE `{$prefixed}` MODIFY `{$col['Field']}` {$col['Type']} COLLATE {$collate} {$null} {$default}";
			error_log( sprintf( 'Rank Math: Changing collation of `%1$s`.`%2$s` column from %3$s to %4$s. SQL: "%5$s"', $prefixed, $col['Field'], $current_collate, $collate, $sql ) ); // phpcs:ignore
			$wpdb->query( $sql ); // phpcs:ignore
			$changed_collations++;
		}

		return $changed_collations;
	}

	/**
	 * Get collation of a specific table.
	 *
	 * @param string $table Table name.
	 * @return string
	 */
	public static function get_table_collation( $table ) {
		global $wpdb;

		$sql = "SHOW CREATE TABLE `{$wpdb->prefix}{$table}`";
		$res = $wpdb->get_row( $sql ); // phpcs:ignore

		if ( ! $res ) {
			return '';
		}

		$table_collate = $res->{'Create Table'};

		// Determine current collation value.
		$current_collate = '';
		if ( preg_match( '/COLLATE=([a-zA-Z0-9_-]+)/', $table_collate, $matches ) ) {
			$current_collate = $matches[1];
		}

		return $current_collate;
	}

	/**
	 * Get default collation.
	 *
	 * @return string
	 */
	public static function get_default_collation() {
		if ( defined( 'DB_COLLATE' ) && DB_COLLATE ) {
			return DB_COLLATE;
		}

		$posts_table_collation = self::get_table_collation( 'posts' );
		if ( $posts_table_collation ) {
			return $posts_table_collation;
		}

		return 'utf8mb4_unicode_ci';
	}

	/**
	 * Retrieve a Database instance by table name.
	 *
	 * @param string $table_name A Database instance id.
	 *
	 * @return Database Database object instance.
	 */
	public static function query_builder( $table_name ) {
		return Database::table( $table_name );
	}

	/**
	 * Check if table exists in db or not.
	 *
	 * @param string $table_name Table name to check for existance.
	 *
	 * @return bool
	 */
	public static function check_table_exists( $table_name ) {
		global $wpdb;

		if ( $wpdb->get_var( $wpdb->prepare( 'SHOW TABLES LIKE %s', $wpdb->esc_like( $wpdb->prefix . $table_name ) ) ) === $wpdb->prefix . $table_name ) {
			return true;
		}

		return false;
	}

	/**
	 * Check if table has more rows than X.
	 *
	 * @since      1.1.16
	 *
	 * @param string $table_name Table name to check.
	 * @param int    $limit      Number of rows to check against.
	 *
	 * @return bool
	 */
	public static function table_size_exceeds( $table_name, $limit ) {
		global $wpdb;

		$check_table = $wpdb->query( "SELECT 1 FROM {$table_name} LIMIT {$limit}, 1" );

		return ! empty( $check_table );
	}
}
