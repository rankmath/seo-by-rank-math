<?php
/**
 *  Google Search Console.
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use Exception;
use MyThemeShop\Helpers\DB;
use function as_unschedule_all_actions;

defined( 'ABSPATH' ) || exit;

/**
 * Console class.
 */
class Console extends Base {

	/**
	 * Constructor.
	 */
	public function __construct() {
		// If console is not connected, ignore all no need to proceed.
		if ( ! \RankMath\Google\Console::is_console_connected() ) {
			return;
		}

		$this->action( 'rank_math/analytics/workflow/console', 'kill_jobs', 5, 0 );
		$this->action( 'rank_math/analytics/workflow/create_tables', 'create_tables' );
		$this->action( 'rank_math/analytics/workflow/console', 'create_tables', 6, 0 );
		$this->action( 'rank_math/analytics/workflow/console', 'create_data_jobs', 10, 3 );
	}

	/**
	 * Unschedule all console data fetch action.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public function kill_jobs() {
		as_unschedule_all_actions( 'rank_math/analytics/get_console_data' );
	}

	/**
	 * Create tables.
	 */
	public function create_tables() {
		global $wpdb;

		$collate = $wpdb->get_charset_collate();
		$table   = 'rank_math_analytics_gsc';

		// Early Bail!!
		if ( DB::check_table_exists( $table ) ) {
			return;
		}

		$schema = "CREATE TABLE {$wpdb->prefix}{$table} (
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
				INDEX rank_position (position)
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
	 *
	 * @param integer $days Number of days to fetch from past.
	 * @param string  $prev Previous saved value.
	 * @param string  $new  New posted value.
	 */
	public function create_data_jobs( $days, $prev, $new ) {
		// Early bail if saved & new profile are same.
		if ( ! $this->is_profile_updated( 'profile', $prev, $new ) ) {
			return;
		}

		// Fetch now.
		$this->create_jobs( $days, 'console' );
	}
}
