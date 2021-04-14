<?php
/**
 *  Install objects.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use Exception;
use RankMath\Helper;
use MyThemeShop\Helpers\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Objects class.
 */
class Objects extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$done = \boolval( get_option( 'rank_math_analytics_installed' ) );
		if ( $done ) {
			return;
		}

		$this->create_tables();
		$this->create_data_job();
		$this->flat_posts();

		update_option( 'rank_math_analytics_installed', true );
	}

	/**
	 * Create tables.
	 */
	public function create_tables() {
		global $wpdb;

		$collate = $wpdb->get_charset_collate();
		$table   = 'rank_math_analytics_objects';

		// Early Bail!!
		if ( DB::check_table_exists( $table ) ) {
			return;
		}

		$schema = "CREATE TABLE {$wpdb->prefix}{$table} (
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
			) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		try {
			dbDelta( $schema );
		} catch ( Exception $e ) { // phpcs:ignore
			// Will log.
		}
	}

	/**
	 * Create jobs to fetch data.
	 */
	public function create_data_job() {
		// Clear old schedule.
		wp_clear_scheduled_hook( 'rank_math/analytics/get_analytics' );

		// Add action for scheduler.
		$task_name = 'rank_math/analytics/data_fetch';
		$fetch_gap = apply_filters( 'rank_math/analytics/fetch_gap', 7 );

		// Schedule new action only when there is no existing action.
		if ( false === as_next_scheduled_action( $task_name ) ) {
			$schedule_in_minute = wp_rand( 3, defined( 'RANK_MATH_PRO_FILE' ) ? 1380 : 4320 );
			$time_to_schedule   = ( strtotime( 'tomorrow' ) + ( $schedule_in_minute * MINUTE_IN_SECONDS ) );
			as_schedule_recurring_action(
				$time_to_schedule,
				DAY_IN_SECONDS * $fetch_gap,
				$task_name,
				[],
				'rank-math'
			);
		}
	}

	/**
	 * Flat posts
	 */
	public function flat_posts() {
		$post_types = Helper::get_accessible_post_types();
		unset( $post_types['attachment'] );

		$ids = get_posts(
			[
				'post_type'      => array_keys( $post_types ),
				'post_status'    => 'publish',
				'fields'         => 'ids',
				'posts_per_page' => -1,
			]
		);

		$counter = 0;
		$chunks  = \array_chunk( $ids, 50 );
		foreach ( $chunks as $chunk ) {
			$counter++;
			as_schedule_single_action(
				time() + ( 60 * ( $counter / 2 ) ),
				'rank_math/analytics/flat_posts',
				[ $chunk ],
				'rank-math'
			);
		}

		// Check for posts.
		as_schedule_single_action(
			time() + ( 60 * ( ( $counter + 1 ) / 2 ) ),
			'rank_math/analytics/flat_posts_completed',
			[],
			'rank-math'
		);

		// Clear cache.
		Workflow::add_clear_cache( time() + ( 60 * ( ( $counter + 2 ) / 2 ) ) );
	}
}
