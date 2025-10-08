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
		$this->load_pro_notice();
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
		$this->run( [ new Bulk_Actions() ] );

		if ( Admin_Helper::is_post_list() || Admin_Helper::is_media_library() || Admin_Helper::is_term_listing() || wp_doing_ajax() ) {
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
		if ( get_option( 'rank_math_already_reviewed' ) ) {
			return;
		}

		$this->run( [ new Ask_Review() ] );
	}

	/**
	 * Load Pro reminder notice.
	 */
	private function load_pro_notice() {
		if ( ! is_main_site() ) {
			return;
		}

		if ( defined( 'RANK_MATH_PRO_FILE' ) || get_option( 'rank_math_already_upgraded' ) ) {
			return;
		}

		$this->run( [ new Pro_Notice() ] );
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
		if ( Helper::is_wizard() ) {
			new Setup_Wizard();
		}
	}
}
