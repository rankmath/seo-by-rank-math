<?php
/**
 * Methods for frontend and backend in admin-only module
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Google\Console;
use MyThemeShop\Helpers\Conditional;
use RankMath\Analytics\Workflow\Jobs;
use RankMath\Analytics\Workflow\Workflow;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Analytics_Common {

	use Hooker;

	/**
	 * The Constructor
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		if ( Helper::has_cap( 'analytics' ) ) {
			$this->action( 'rank_math/admin_bar/items', 'admin_bar_items', 11 );
		}

		new GTag();
		$this->action( 'plugins_loaded', 'maybe_init_email_reports', 15 );
		$this->action( 'init', 'maybe_enable_email_reports', 20 );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-general_options', 'maybe_update_report_schedule', 20, 3 );

		Jobs::get();
		Workflow::get();

		$this->action( 'rest_api_init', 'init_rest_api' );
		$this->filter( 'rank_math/webmaster/google_verify', 'add_site_verification' );

		$this->filter( 'rank_math/tools/analytics_clear_caches', 'analytics_clear_caches' );
		$this->filter( 'rank_math/tools/analytics_reindex_posts', 'analytics_reindex_posts' );
		$this->filter( 'wp_helpers_notifications_render', 'replace_notice_link', 10, 3 );
	}

	/**
	 * Return site verification code.
	 *
	 * @param string $content If any code from setting.
	 *
	 * @return string
	 */
	public function add_site_verification( $content ) {
		$code = get_transient( 'rank_math_google_site_verification' );

		return ! empty( $code ) ? $code : $content;
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$controllers = [
			new Rest(),
		];

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Add admin bar item.
	 *
	 * @param Admin_Bar_Menu $menu Menu class instance.
	 */
	public function admin_bar_items( $menu ) {
		$dot_color = '#ed5e5e';
		if ( Console::is_console_connected() ) {
			$dot_color = '#11ac84';
		}

		$menu->add_sub_menu(
			'analytics',
			[
				'title'    => esc_html__( 'Analytics', 'rank-math' ) . '<span class="rm-menu-new update-plugins" style="background: ' . $dot_color . ';margin-left: 5px;min-width: 10px;height: 10px;margin-bottom: -1px;display: inline-block;border-radius: 5px;"><span class="plugin-count"></span></span>',
				'href'     => Helper::get_admin_url( 'analytics' ),
				'meta'     => [ 'title' => esc_html__( 'Review analytics and sitemaps', 'rank-math' ) ],
				'priority' => 20,
			]
		);
	}

	/**
	 * Purge cache.
	 *
	 * @return string
	 */
	public function analytics_clear_caches() {
		DB::purge_cache();
		return __( 'Analytics cache cleared.', 'rank-math' );
	}

	/**
	 * ReIndex posts.
	 *
	 * @return string
	 */
	public function analytics_reindex_posts() {
		// Clear all objects data.
		DB::objects()
			->truncate();

		// Clear all metadata related to object.
		DB::table( 'postmeta' )
			->where( 'meta_key', 'rank_math_analytic_object_id' )
			->delete();

		// Start reindexing posts.
		( new \RankMath\Analytics\Workflow\Objects() )->flat_posts();

		return __( 'Post re-index in progress.', 'rank-math' );
	}

	/**
	 * Init Email Reports class if the option is enabled.
	 *
	 * @return void
	 */
	public function maybe_init_email_reports() {
		if ( Helper::get_settings( 'general.console_email_reports' ) ) {
			new Email_Reports();
		}
	}


	/**
	 * Enable the email reports option if the `enable_email_reports` param is set.
	 *
	 * @return void
	 */
	public function maybe_enable_email_reports() {
		if ( ! Helper::has_cap( 'analytics' ) ) {
			return;
		}

		if ( ! isset( $_GET['_wpnonce'] ) || ! wp_verify_nonce( $_GET['_wpnonce'], 'enable_email_reports' ) ) {
			return;
		}

		if ( ! empty( $_GET['enable_email_reports'] ) ) {
			$all_opts = rank_math()->settings->all_raw();
			$general  = $all_opts['general'];

			$general['console_email_reports'] = 'on';

			Helper::update_all_settings( $general, null, null );
			rank_math()->settings->reset();
			$this->schedule_email_reporting();

			Helper::remove_notification( 'rank_math_analytics_new_email_reports' );
			Helper::redirect( remove_query_arg( 'enable_email_reports' ) );
			die();
		}
	}

	/**
	 * Add/remove/change scheduled action when the report on/off or the frequency options are changed.
	 *
	 * @param int    $object_id The ID of the current object.
	 * @param array  $updated   Array of field IDs that were updated.
	 *                          Will only include field IDs that had values change.
	 * @param object $cmb       CMB object.
	 */
	public function maybe_update_report_schedule( $object_id, $updated, $cmb ) {
		// Early bail if our options are not changed.
		if ( ! in_array( 'console_email_reports', $updated, true ) && ! in_array( 'console_email_frequency', $updated, true ) ) {
			return;
		}

		as_unschedule_all_actions( 'rank_math/analytics/email_report_event', [], 'rank-math' );
		$values = $cmb->get_sanitized_values( $_POST ); // phpcs:ignore
		if ( 'off' === $values['console_email_reports'] ) {
			return;
		}

		$frequency = isset( $values['console_email_frequency'] ) ? $values['console_email_frequency'] : 'monthly';
		$this->schedule_email_reporting( $frequency );
	}

	/**
	 * Replace link inside notice dynamically to avoid issues with the nonce.
	 *
	 * @param string $output  Notice output.
	 * @param string $message Notice message.
	 * @param array  $options Notice options.
	 *
	 * @return string
	 */
	public function replace_notice_link( $output, $message, $options ) {
		$url    = wp_nonce_url( Helper::get_admin_url( 'options-general&enable_email_reports=1#setting-panel-analytics' ), 'enable_email_reports' );
		$output = str_replace( '###ENABLE_EMAIL_REPORTS###', $url, $output );
		return $output;
	}

	/**
	 * Schedule Email Reporting.
	 *
	 * @param string $frequency  The frequency in which the action should run.
	 * @return void
	 */
	private function schedule_email_reporting( $frequency = 'monthly' ) {
		$interval_days = Email_Reports::get_period_from_frequency( $frequency );
		$midnight      = strtotime( 'tomorrow midnight' );
		as_schedule_recurring_action( $midnight, $interval_days * DAY_IN_SECONDS, 'rank_math/analytics/email_report_event', [], 'rank-math' );
	}
}
