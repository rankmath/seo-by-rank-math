<?php
/**
 * The admin bootstrap of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Updates;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Conditional;
use RankMath\Search_Console\Search_Console;

defined( 'ABSPATH' ) || exit;

/**
 * Admin_Init class.
 *
 * @codeCoverageIgnore
 */
class Admin_Init {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		rank_math()->admin        = new Admin();
		rank_math()->admin_assets = new Assets();

		$this->load_review_reminders();
		$this->load_setup_wizard();
		$this->load_post_columns_and_filters();

		$this->run(
			[
				rank_math()->admin,
				rank_math()->admin_assets,
				new Admin_Menu(),
				new Option_Center(),
				new Notices(),
				new CMB2_Fields(),
				new Deactivate_Survey(),
				new Metabox\Metabox(),
				new Import_Export(),
				new Updates(),
				new Watcher(),
			]
		);

		/**
		 * Fires when admin is loaded.
		 */
		$this->do_action( 'admin/loaded' );
	}

	/**
	 * Load out post list and edit screen class.
	 */
	private function load_post_columns_and_filters() {
		if ( Admin_Helper::is_post_list() || Admin_Helper::is_media_library() || wp_doing_ajax() ) {
			$this->run(
				[
					new Post_Columns(),
					new Post_Filters(),
				]
			);
		}
	}

	/**
	 * Load review tab in metabox & footer notice.
	 */
	private function load_review_reminders() {
		if (
			get_option( 'rank_math_already_reviewed' ) ||
			get_option( 'rank_math_install_date' ) + ( 2 * WEEK_IN_SECONDS ) > current_time( 'timestamp' )
		) {
			return;
		}

		$this->run( [ new Ask_Review() ] );
	}

	/**
	 * Run all the runners.
	 *
	 * @param array $runners Instances of runner classes.
	 */
	private function run( $runners ) {
		foreach ( $runners as $runner ) {
			$runner->hooks();
		}
	}

	/**
	 * Load setup wizard.
	 */
	private function load_setup_wizard() {
		if ( filter_input( INPUT_GET, 'page' ) === 'rank-math-wizard' || filter_input( INPUT_POST, 'action' ) === 'rank_math_save_wizard' ) {
			new Setup_Wizard();
		}
	}
}
