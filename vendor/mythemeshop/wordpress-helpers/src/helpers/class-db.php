<?php
/**
 * The database helpers.
 *
 * @since      1.0.0
 * @package    MyThemeShop
 * @subpackage MyThemeShop\Helpers
 * @author     MyThemeShop <admin@mythemeshop.com>
 */

namespace MyThemeShop\Helpers;

use MyThemeShop\Database\Database;

/**
 * DB class.
 */
class DB {

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
}
