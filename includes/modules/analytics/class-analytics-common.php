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
use RankMath\Google\Authentication;
use RankMath\Analytics\Workflow\Jobs;
use RankMath\Analytics\Workflow\Workflow;
use MyThemeShop\Helpers\Conditional;
use MyThemeShop\Helpers\Str;

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

		// Show Analytics block in the Dashboard widget only if account is connected or user has permissions.
		if ( Helper::has_cap( 'analytics' ) && Authentication::is_authorized() ) {
			$this->action( 'rank_math/dashboard/widget', 'dashboard_widget' );
		}

		new GTag();
		new Analytics_Stats();
		$this->action( 'plugins_loaded', 'maybe_init_email_reports', 15 );
		$this->action( 'init', 'maybe_enable_email_reports', 20 );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-general_options', 'maybe_update_report_schedule', 20, 3 );

		Jobs::get();
		Workflow::get();

		$this->action( 'rest_api_init', 'init_rest_api' );
		$this->filter( 'rank_math/webmaster/google_verify', 'add_site_verification' );

		$this->filter( 'rank_math/tools/analytics_clear_caches', 'analytics_clear_caches' );
		$this->filter( 'rank_math/tools/analytics_reindex_posts', 'analytics_reindex_posts' );
		$this->filter( 'rank_math/tools/analytics_fix_collations', 'analytics_fix_collations' );
		$this->filter( 'wp_helpers_notifications_render', 'replace_notice_link', 10, 3 );
	}

	/**
	 * Add stats widget into admin dashboard.
	 */
	public function dashboard_widget() {
		?>
		<h3>
			<?php esc_html_e( 'Analytics', 'rank-math' ); ?>
			<span><?php esc_html_e( 'Last 30 Days', 'rank-math' ); ?></span>
			<a href="<?php echo esc_url( Helper::get_admin_url( 'analytics' ) ); ?>" class="rank-math-view-report" title="<?php esc_html_e( 'View Report', 'rank-math' ); ?>">
				<i class="dashicons dashicons-ellipsis"></i>
			</a>
		</h3>
		<div class="rank-math-dashabord-block items-4">
			<?php
			$items = $this->get_dashboard_widget_items();
			foreach ( $items as $label => $item ) {
				if ( ! $item['value'] ) {
					continue;
				}
				?>
				<div>
					<h4>
						<?php echo esc_html( $item['label'] ); ?>
						<span class="rank-math-tooltip">
							<em class="dashicons-before dashicons-editor-help"></em>
							<span>
								<?php echo esc_html( $item['desc'] ); ?>
							</span>
						</span>
					</h4>
					<?php $this->get_analytic_block( $item['data'], ! empty( $item['revert'] ) ); ?>
				</div>
			<?php } ?>
		</div>
		<?php
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
	 * Fix table & column collations.
	 *
	 * @return string
	 */
	public function analytics_fix_collations() {
		$tables = [
			'rank_math_analytics_ga',
			'rank_math_analytics_gsc',
			'rank_math_analytics_keyword_manager',
			'rank_math_analytics_inspections',
		];

		$objects_coll = Helper::get_table_collation( 'rank_math_analytics_objects' );
		$changed      = 0;
		foreach ( $tables as $table ) {
			$changed += (int) Helper::check_collation( $table, 'all', $objects_coll );
		}

		return $changed ? sprintf(
			/* translators: %1$d: number of changes, %2$s: new collation. */
			_n( '%1$d collation changed to %2$s.', '%1$d collations changed to %2$s.', $changed, 'rank-math' ),
			$changed,
			'`' . $objects_coll . '`'
		) : __( 'No collation mismatch to fix.', 'rank-math' );
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
	 * Get Dashboard Widget items.
	 */
	private function get_dashboard_widget_items() {
		// Get stats info within last 30 days.
		Stats::get()->set_date_range( '-30 days' );
		$data         = Stats::get()->get_widget();
		$analytics    = get_option( 'rank_math_google_analytic_options' );
		$is_connected = ! empty( $analytics ) && ! empty( $analytics['view_id'] );
		return [
			'search-traffic'    => [
				'label' => __( 'Search Traffic', 'rank-math' ),
				'desc'  => __( 'This is the number of pageviews carried out by visitors from Google.', 'rank-math' ),
				'value' => $is_connected && defined( 'RANK_MATH_PRO_FILE' ),
				'data'  => isset( $data->pageviews ) ? $data->pageviews : '',
			],
			'total-impressions' => [
				'label' => __( 'Total Impressions', 'rank-math' ),
				'desc'  => __( 'How many times your site showed up in the search results.', 'rank-math' ),
				'value' => true,
				'data'  => $data->impressions,
			],
			'total-clicks'      => [
				'label' => __( 'Total Clicks', 'rank-math' ),
				'desc'  => __( 'This is the number of pageviews carried out by visitors from Google.', 'rank-math' ),
				'value' => ! $is_connected || ( $is_connected && ! defined( 'RANK_MATH_PRO_FILE' ) ),
				'data'  => $data->clicks,
			],
			'total-keywords'    => [
				'label' => __( 'Total Keywords', 'rank-math' ),
				'desc'  => __( 'Total number of keywords your site ranking below 100 position.', 'rank-math' ),
				'value' => true,
				'data'  => $data->keywords,
			],
			'average-position'  => [
				'label'  => __( 'Average Position', 'rank-math' ),
				'desc'   => __( 'Average position of all the ranking keywords below 100 position.', 'rank-math' ),
				'value'  => true,
				'revert' => true,
				'data'   => $data->position,
			],
		];
	}

	/**
	 * Get analytic block
	 *
	 * @param object  $item   Item.
	 * @param boolean $revert Flag whether to revert difference icon or not.
	 */
	private function get_analytic_block( $item, $revert = false ) {
		$is_negative = abs( $item['difference'] ) !== $item['difference'];
		$diff_class  = 'up';
		if ( ( ! $revert && $is_negative ) || ( $revert && ! $is_negative && $item['difference'] > 0 ) ) {
			$diff_class = 'down';
		}
		?>
		<div class="rank-math-item-numbers">
			<strong class="text-large" title="<?php echo esc_html( Str::human_number( $item['total'] ) ); ?>"><?php echo esc_html( Str::human_number( $item['total'] ) ); ?></strong>
			<span class="rank-math-item-difference <?php echo esc_attr( $diff_class ); ?>" title="<?php echo esc_html( Str::human_number( abs( $item['difference'] ) ) ); ?>"><?php echo esc_html( Str::human_number( abs( $item['difference'] ) ) ); ?></span>
		</div>
		<?php
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
