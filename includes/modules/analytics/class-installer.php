<?php
/**
 * The Analytics Module
 *
 * @since      1.4.0
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
		$this->create_tables();

		// Clear schedule.
		wp_clear_scheduled_hook( 'rank_math/analytics/get_analytics' );

		// Add action for scheduler.
		if ( false === as_next_scheduled_action( 'rank_math/analytics/daily_tasks' ) ) {
			as_schedule_recurring_action( strtotime( 'tomorrow' ) + 180, DAY_IN_SECONDS * 7, 'rank_math/analytics/daily_tasks' );
		}

		if ( $flat ) {
			Data_Fetcher::get()->flat_posts();
		}
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
				INDEX analytics_query (query),
				INDEX analytics_page (page),
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
				INDEX analytics_object_page (page)
			) $collate;",

			// Link Storage.
			"CREATE TABLE IF NOT EXISTS {$prefix}keyword_manager (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				keyword VARCHAR(1000) NOT NULL,
				collection VARCHAR(200) NULL,
				is_active TINYINT(1) NOT NULL DEFAULT 1,
				PRIMARY KEY (id)
			) $collate;",

			// Link meta.
			"CREATE TABLE IF NOT EXISTS {$prefix}object_links (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				object_id BIGINT(20) UNSIGNED NOT NULL,
				link VARCHAR(255) NOT NULL,
				type VARCHAR(255) NOT NULL,
				rel VARCHAR(255) NOT NULL,
				status TINYINT NOT NULL,
				PRIMARY KEY (id)
			) $collate;",

			"CREATE TABLE IF NOT EXISTS {$prefix}ga (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				page VARCHAR(500) NOT NULL,
				created TIMESTAMP NOT NULL,
				pageviews MEDIUMINT(6) NOT NULL,
				visitors MEDIUMINT(6) NOT NULL,
				PRIMARY KEY (id),
				INDEX analytics_object_analytics (page)
			) $collate;",

			"CREATE TABLE IF NOT EXISTS {$prefix}adsense (
				id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
				created TIMESTAMP NOT NULL,
				earnings DOUBLE NOT NULL DEFAULT 0,
				PRIMARY KEY (id)
			) $collate;",
		];

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $table_schema as $table ) {
			try {
				dbDelta( $table );
			} catch ( Exception $e ) {
				// Will log.
			}
		}
	}
}
