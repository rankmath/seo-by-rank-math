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
use RankMath\Helpers\DB;
use RankMath\Google\Console as GoogleConsole;
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

		$this->create_tables();

		// If console is not connected, ignore all no need to proceed.
		if ( ! GoogleConsole::is_console_connected() ) {
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
				id bigint(20) unsigned NOT NULL auto_increment,
				created timestamp NOT NULL,
				query varchar(1000) NOT NULL,
				page varchar(500) NOT NULL,
				clicks mediumint(6) NOT NULL,
				impressions mediumint(6) NOT NULL,
				position double NOT NULL,
				ctr double NOT NULL,
				PRIMARY KEY  (id),
				KEY analytics_query (query(190)),
				KEY analytics_page (page(190)),
				KEY clicks (clicks),
				KEY rank_position (position)
			) $collate;";

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		try {
			dbDelta( $schema );
		} catch ( Exception $e ) { // phpcs:ignore
			// Will log.
		}

		// Make sure that collations match the objects table.
		$objects_coll = DB::get_table_collation( 'rank_math_analytics_objects' );
		DB::check_collation( $table, 'all', $objects_coll );
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

		update_option( 'rank_math_analytics_first_fetch', 'fetching' );

		// Fetch now.
		$this->schedule_single_action( $days, 'console' );
	}
}
