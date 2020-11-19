<?php
/**
 * The Analytics Module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

defined( 'ABSPATH' ) || exit;

use Exception;

/**
 * Installer class.
 */
class Installer {

	/**
	 * Install routine.
	 *
	 * @param bool $flat Flat posts or not.
	 */
	public function install( $flat = true ) {
		$done = \boolval( get_option( 'rank_math_analytics_installed' ) );
		if ( $done ) {
			return;
		}

		$this->create_tables();

		// Clear schedule.
		wp_clear_scheduled_hook( 'rank_math/analytics/get_analytics' );

		// Add action for scheduler.
		$fetch_gap = apply_filters( 'rank_math/analytics/fetch_gap', 7 );
		if ( false === as_next_scheduled_action( 'rank_math/analytics/daily_tasks' ) ) {
			as_schedule_recurring_action( strtotime( 'tomorrow' ) + 180, DAY_IN_SECONDS * $fetch_gap, 'rank_math/analytics/daily_tasks' );
		}

		if ( $flat ) {
			Data_Fetcher::get()->flat_posts();
		}

		update_option( 'rank_math_analytics_installed', true );
	}

	/**
	 * Create tables
	 */
	private function create_tables() {
		global $wpdb;

		$collate      = $wpdb->get_charset_collate();
		$prefix       = $wpdb->prefix . 'rank_math_analytics_';
		$table_schema = [

			"CREATE TABLE IF NOT EXISTS {$prefix}gsc (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created TIMESTAMP NOT NULL,
				query VARCHAR(1000) NOT NULL,
				page VARCHAR(500) NOT NULL,
				clicks MEDIUMINT(6) NOT NULL,
				impressions MEDIUMINT(6) NOT NULL,
				position DOUBLE NOT NULL,
				ctr DOUBLE NOT NULL,
				PRIMARY KEY (id),
				INDEX analytics_query (query(190)),
				INDEX analytics_page (page(190)),
				INDEX clicks (clicks),
				INDEX position (position)
			) $collate;",

			"CREATE TABLE IF NOT EXISTS {$prefix}objects (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created TIMESTAMP NOT NULL,
				title TEXT NOT NULL,
				page VARCHAR(500) NOT NULL,
				object_type VARCHAR(100) NOT NULL,
				object_subtype VARCHAR(100) NOT NULL,
				object_id BIGINT(20) UNSIGNED NOT NULL,
				primary_key VARCHAR(255) NOT NULL,
				seo_score TINYINT NOT NULL DEFAULT 0,
				page_score TINYINT NOT NULL DEFAULT 0,
				is_indexable TINYINT(1) NOT NULL DEFAULT 1,
				schemas_in_use VARCHAR(500),
				desktop_interactive DOUBLE DEFAULT 0,
				desktop_pagescore DOUBLE DEFAULT 0,
				mobile_interactive DOUBLE DEFAULT 0,
				mobile_pagescore DOUBLE DEFAULT 0,
				pagespeed_refreshed TIMESTAMP,
				PRIMARY KEY (id),
				INDEX analytics_object_page (page(190))
			) $collate;",
		];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $table_schema as $table ) {
			try {
				dbDelta( $table );
			} catch ( Exception $e ) { // phpcs:ignore
				// Will log.
			}
		}
	}
}
