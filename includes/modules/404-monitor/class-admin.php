<?php
/**
 * The admin-side code for the 404 Monitor module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Monitor
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Monitor;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Admin\Page;
use RankMath\Helpers\Arr;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Admin class.
 */
class Admin extends Base {

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	public $directory;

	/**
	 * WP_List_Table class name.
	 *
	 * @var string
	 */
	public $table;

	/**
	 * Screen options.
	 *
	 * @var array
	 */
	public $screen_options = [];

	/**
	 * Page object.
	 *
	 * @var Page
	 */
	public $page;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$directory = __DIR__;
		$this->config(
			[
				'id'             => '404-monitor',
				'directory'      => $directory,
				'table'          => 'RankMath\Monitor\Table',
				'screen_options' => [
					'id'      => 'rank_math_404_monitor_per_page',
					'default' => 100,
				],
			]
		);
		parent::__construct();

		if ( $this->page->is_current_page() ) {
			$this->action( 'init', 'init' );
		}

		if ( Helper::has_cap( '404_monitor' ) ) {
			$this->filter( 'rank_math/settings/general', 'add_settings' );
		}
	}

	/**
	 * Initialize.
	 */
	public function init() {
		$action = Helper::get_request_action();
		if ( false === $action || ! in_array( $action, [ 'delete', 'clear_log' ], true ) ) {
			return;
		}

		if ( ! check_admin_referer( 'bulk-events' ) ) {
			check_admin_referer( '404_delete_log', 'security' );
		}

		$action = 'do_' . $action;
		$this->$action();
	}

	/**
	 * Delete selected log.
	 */
	protected function do_delete() {
		$log = Param::request( 'log', '', FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		if ( empty( $log ) ) {
			return;
		}

		$count = DB::delete_log( $log );
		if ( $count > 0 ) {
			Helper::add_notification(
				/* translators: delete counter */
				sprintf( esc_html__( '%d log(s) deleted.', 'rank-math' ), $count ),
				[ 'type' => 'success' ]
			);
		}
	}

	/**
	 * Clears all 404 logs, by truncating the log table.
	 * Fired with the `$this->$action();` line inside the `init()` method.
	 */
	protected function do_clear_log() {
		$count = DB::get_count();
		DB::clear_logs();

		Helper::add_notification(
			/* translators: delete counter */
			sprintf( esc_html__( 'Log cleared - %d items deleted.', 'rank-math' ), $count ),
			[ 'type' => 'success' ]
		);
	}

	/**
	 * Register the 404 Monitor admin page.
	 */
	public function register_admin_page() {

		$dir = $this->directory . '/views/';
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-404-monitor',
			esc_html__( '404 Monitor', 'rank-math' ),
			[
				'position'   => 30,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_404_monitor',
				'render'     => $dir . 'main.php',
				'help'       => [
					'404-overview'       => [
						'title' => esc_html__( 'Overview', 'rank-math' ),
						'view'  => $dir . 'help-tab-overview.php',
					],
					'404-screen-content' => [
						'title' => esc_html__( 'Screen Content', 'rank-math' ),
						'view'  => $dir . 'help-tab-screen-content.php',
					],
					'404-actions'        => [
						'title' => esc_html__( 'Available Actions', 'rank-math' ),
						'view'  => $dir . 'help-tab-actions.php',
					],
					'404-bulk'           => [
						'title' => esc_html__( 'Bulk Actions', 'rank-math' ),
						'view'  => $dir . 'help-tab-bulk.php',
					],
				],
				'assets'     => [
					'styles'  => [
						'rank-math-common'      => '',
						'rank-math-404-monitor' => $uri . '/assets/css/404-monitor.css',
					],
					'scripts' => [ 'rank-math-404-monitor' => $uri . '/assets/js/404-monitor.js' ],
				],
			]
		);

		if ( $this->page->is_current_page() ) {
			Helper::add_json( 'logConfirmClear', esc_html__( 'Are you sure you wish to delete all 404 error logs?', 'rank-math' ) );
			Helper::add_json( 'redirectionsUri', Helper::get_admin_url( 'redirections' ) );
		}
	}

	/**
	 * Add module settings tab in the General Settings.
	 *
	 * @param array $tabs Array of option panel tabs.
	 *
	 * @return array
	 */
	public function add_settings( $tabs ) {

		Arr::insert(
			$tabs,
			[
				'404-monitor' => [
					'icon'  => 'rm-icon rm-icon-404',
					'title' => esc_html__( '404 Monitor', 'rank-math' ),
					/* translators: 1. Link to KB article 2. Link to redirection setting scree */
					'desc'  => sprintf( esc_html__( 'Monitor broken pages that ruin user-experience and affect SEO. %s.', 'rank-math' ), '<a href="' . KB::get( '404-monitor-settings', 'Options Panel 404 Monitor Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
					'file'  => $this->directory . '/views/options.php',
				],
			],
			7
		);

		return $tabs;
	}

	/**
	 * Output page title actions.
	 *
	 * @return void
	 */
	public function page_title_actions() {
		$actions = [
			'settings'   => [
				'class' => 'page-title-action',
				'href'  => Helper::get_admin_url( 'options-general#setting-panel-404-monitor' ),
				'label' => __( 'Settings', 'rank-math' ),
			],
			'learn_more' => [
				'class' => 'page-title-action',
				'href'  => KB::get( '404-monitor', '404 Page Learn More Button' ),
				'label' => __( 'Learn More', 'rank-math' ),
			],
		];

		/**
		 * Filters the title actions available on the 404 Monitor page.
		 *
		 * @param array $actions Multidimensional array of actions to show.
		 */
		$actions = $this->do_filter( '404_monitor/page_title_actions', $actions );

		foreach ( $actions as $action_name => $action ) {
			?>
				<a class="<?php echo esc_attr( $action['class'] ); ?> rank-math-404-monitor-<?php echo esc_attr( $action_name ); ?>" href="<?php echo esc_attr( $action['href'] ); ?>" target="_blank"><?php echo esc_attr( $action['label'] ); ?></a>
			<?php
		}
	}
}
