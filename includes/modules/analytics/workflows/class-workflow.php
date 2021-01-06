<?php
/**
 *  Workflow.
 *
 * @since      1.0.54
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics\Workflow;

use RankMath\Traits\Hooker;
use function as_enqueue_async_action;
use function as_unschedule_all_actions;

defined( 'ABSPATH' ) || exit;

/**
 * Workflow class.
 */
class Workflow {

	use Hooker;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Workflow
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Workflow ) ) {
			$instance = new Workflow();
			$instance->hooks();
		}

		return $instance;
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		// Common.
		$this->action( 'rank_math/analytics/workflow', 'maybe_first_install', 5, 0 );
		$this->action( 'rank_math/analytics/workflow', 'start_workflow', 10, 4 );
		$this->action( 'rank_math/analytics/workflow/create_tables', 'create_tables_only', 5 );

		// Console.
		$this->action( 'rank_math/analytics/workflow/console', 'init_console_workflow', 5, 0 );
	}

	/**
	 * Maybe first install.
	 */
	public function maybe_first_install() {
		new \RankMath\Analytics\Workflow\Objects();
	}

	/**
	 * Init Console workflow
	 */
	public function init_console_workflow() {
		new \RankMath\Analytics\Workflow\Console();
	}

	/**
	 * Create tables only.
	 */
	public function create_tables_only() {
		( new \RankMath\Analytics\Workflow\Objects() )->create_tables();
		new \RankMath\Analytics\Workflow\Console();
	}

	/**
	 * Service workflow
	 *
	 * @param string  $action Action to perform.
	 * @param integer $days   Number of days to fetch from past.
	 * @param string  $prev   Previous saved value.
	 * @param string  $new    New posted value.
	 */
	public function start_workflow( $action, $days, $prev, $new ) {
		do_action(
			'rank_math/analytics/workflow/' . $action,
			$days,
			$prev,
			$new
		);
	}

	/**
	 * Service workflow
	 *
	 * @param string  $action Action to perform.
	 * @param integer $days   Number of days to fetch from past.
	 * @param string  $prev   Previous saved value.
	 * @param string  $new    New posted value.
	 */
	public static function do_workflow( $action, $days, $prev = null, $new = null ) {
		as_enqueue_async_action(
			'rank_math/analytics/workflow',
			[
				'action' => $action,
				'days'   => $days,
				'prev'   => $prev,
				'new'    => $new,
			],
			'workflow'
		);
	}

	/**
	 * Kill all workflows
	 *
	 * Stop processing queue items, clear cronjob and delete all batches.
	 */
	public static function kill_workflows() {
		as_unschedule_all_actions( 'rank_math/analytics/workflow' );
		as_unschedule_all_actions( 'rank_math/analytics/clear_cache' );
		as_unschedule_all_actions( 'rank_math/analytics/get_console_data' );
		as_unschedule_all_actions( 'rank_math/analytics/get_analytics_data' );
		as_unschedule_all_actions( 'rank_math/analytics/get_adsense_data' );

		do_action( 'rank_math/analytics/clear_cache' );
	}

	/**
	 * Add clear cache job.
	 *
	 * @param int $time Timestamp to add job for.
	 */
	public static function add_clear_cache( $time ) {
		as_unschedule_all_actions( 'rank_math/analytics/clear_cache' );
		as_schedule_single_action(
			$time,
			'rank_math/analytics/clear_cache',
			[],
			'rank-math'
		);
	}
}
