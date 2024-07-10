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
use RankMath\Traits\Hooker;
use RankMath\Analytics\DB as AnalyticsDB;
use RankMath\Analytics\Url_Inspection;
use RankMath\Google\Console;

use function as_unschedule_all_actions;

defined( 'ABSPATH' ) || exit;

/**
 * Inspections class.
 */
class Inspections {

	use Hooker;

	/**
	 * API Limit.
	 * 600 requests per minute, 2000 per day.
	 * We can ignore the per-minute limit, since we will use a few seconds delay after each request.
	 */
	const API_LIMIT = 2000;

	/**
	 * Interval between requests.
	 */
	const REQUEST_GAP_SECONDS = 7;

	/**
	 * Constructor.
	 */
	public function __construct() {

		$this->create_tables();

		// If console is not connected, ignore all, no need to proceed.
		if ( ! Console::is_console_connected() ) {
			return;
		}

		$this->action( 'rank_math/analytics/workflow/create_tables', 'create_tables' );
		$this->action( 'rank_math/analytics/workflow/inspections', 'create_tables', 6, 0 );
		$this->action( 'rank_math/analytics/workflow/inspections', 'create_data_jobs', 10, 0 );
	}

	/**
	 * Unschedule all inspections data fetch action.
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public static function kill_jobs() {
		as_unschedule_all_actions( 'rank_math/analytics/get_inspections_data' );
	}

	/**
	 * Create tables.
	 */
	public function create_tables() {
		global $wpdb;

		$collate = $wpdb->get_charset_collate();
		$table   = 'rank_math_analytics_inspections';

		// Early Bail!!
		if ( DB::check_table_exists( $table ) ) {
			return;
		}

		$schema = "CREATE TABLE {$wpdb->prefix}{$table} (
				id bigint(20) unsigned NOT NULL auto_increment,
				page varchar(500) NOT NULL,
				created timestamp NOT NULL, 
                index_verdict varchar(64) NOT NULL,            /* PASS, PARTIAL, FAIL, NEUTRAL, VERDICT_UNSPECIFIED */
                indexing_state varchar(64) NOT NULL,           /* INDEXING_ALLOWED, BLOCKED_BY_META_TAG, BLOCKED_BY_HTTP_HEADER, BLOCKED_BY_ROBOTS_TXT, INDEXING_STATE_UNSPECIFIED */
                coverage_state text NOT NULL,                  /* String, e.g. 'Submitted and indexed'. */
                page_fetch_state varchar(64) NOT NULL,         /* SUCCESSFUL, SOFT_404, BLOCKED_ROBOTS_TXT, NOT_FOUND, ACCESS_DENIED, SERVER_ERROR, REDIRECT_ERROR, ACCESS_FORBIDDEN, BLOCKED_4XX, INTERNAL_CRAWL_ERROR, INVALID_URL, PAGE_FETCH_STATE_UNSPECIFIED */
                robots_txt_state varchar(64) NOT NULL,         /* ALLOWED, DISALLOWED, ROBOTS_TXT_STATE_UNSPECIFIED */
                rich_results_verdict varchar(64) NOT NULL,     /* PASS, PARTIAL, FAIL, NEUTRAL, VERDICT_UNSPECIFIED */
                rich_results_items longtext NOT NULL,          /* JSON */
                last_crawl_time timestamp NOT NULL,
                crawled_as varchar(64) NOT NULL,               /* DESKTOP, MOBILE, CRAWLING_USER_AGENT_UNSPECIFIED */
                google_canonical text NOT NULL,                /* Google-chosen canonical URL. */
                user_canonical text NOT NULL,                  /* Canonical URL declared on-page. */
                sitemap text NOT NULL,                         /* Sitemap URL. */
                referring_urls longtext NOT NULL,              /* JSON */
				raw_api_response longtext NOT NULL,            /* JSON */
                PRIMARY KEY  (id),
				KEY analytics_object_page (page(190)),
				KEY created (created),
                KEY index_verdict (index_verdict),
                KEY page_fetch_state (page_fetch_state),
                KEY robots_txt_state (robots_txt_state),
                KEY rich_results_verdict (rich_results_verdict)
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
	 */
	public function create_data_jobs() {
		// If there are jobs left from the previous queue, don't create new jobs.
		if ( as_has_scheduled_action( 'rank_math/analytics/get_inspections_data' ) ) {
			return;
		}

		// If the option is disabled, don't create jobs.
		if ( ! Url_Inspection::is_enabled() ) {
			return;
		}

		$inspections_table = AnalyticsDB::inspections()->table;
		$objects_table     = AnalyticsDB::objects()->table;

		$objects = AnalyticsDB::objects()
			->select( [ "$objects_table.id", "$objects_table.page", "$inspections_table.created" ] )
			->leftJoin( $inspections_table, "$inspections_table.page", "$objects_table.page" )
			->where( "$objects_table.is_indexable", 1 )
			->orderBy( "$inspections_table.created", 'ASC' )
			->get();

		$count = 0;
		foreach ( $objects as $object ) {
			$count++;
			$time = time() + ( $count * self::REQUEST_GAP_SECONDS );
			if ( $count > self::API_LIMIT ) {
				$delay_days = floor( $count / self::API_LIMIT );
				$time       = strtotime( "+{$delay_days} days", $time );
			}

			as_schedule_single_action( $time, 'rank_math/analytics/get_inspections_data', [ $object->page ], 'rank-math' );
		}
	}

}
