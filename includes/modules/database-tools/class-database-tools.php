<?php
/**
 * The Database_Tools is responsible for the Database Tools inside Status & Tools.
 *
 * @package    RankMath
 * @subpackage RankMath\Database_Tools
 */

namespace RankMath\Tools;

use RankMath\Helper;
use RankMath\Installer;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Database_Tools class.
 */
class Database_Tools {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() || ! Helper::is_advanced_mode() ) {
			return;
		}

		Yoast_Blocks::get();
		AIOSEO_Blocks::get();
		Remove_Schema::get();
		Update_Score::get();
		$this->hooks();
	}

	/**
	 * Register version control hooks.
	 */
	public function hooks() {
		if ( ! Helper::is_plugin_active_for_network() || current_user_can( 'manage_options' ) ) {
			$this->filter( 'rank_math/tools/pages', 'add_tools_page', 11 );
		}

		if ( Conditional::is_rest() ) {
			foreach ( $this->get_tools() as $id => $tool ) {
				if ( ! method_exists( $this, $id ) ) {
					continue;
				}

				add_filter( 'rank_math/tools/' . $id, [ $this, $id ] );
			}
		}
	}

	/**
	 * Display Tools data.
	 */
	public function display() {
		?>
		<table class='rank-math-status-table striped rank-math-tools-table widefat rank-math-box'>

			<tbody class='tools'>

				<?php foreach ( $this->get_tools() as $id => $tool ) : ?>
					<tr class='<?php echo sanitize_html_class( $id ); ?>'>
						<th>
							<h4 class='name'><?php echo esc_html( $tool['title'] ); ?></h4>
							<p class="description"><?php echo esc_html( $tool['description'] ); ?></p>
						</th>
						<td class='run-tool'>
							<a href='#' class='button button-large button-link-delete tools-action' data-action='<?php echo esc_attr( $id ); ?>' data-confirm="<?php echo isset( $tool['confirm_text'] ) ? esc_attr( $tool['confirm_text'] ) : 'false'; ?>"><?php echo esc_html( $tool['button_text'] ); ?></a>
						</td>
					</tr>
				<?php endforeach; ?>

			</tbody>

		</table>
		<?php
	}

	/**
	 * Function to clear all the transients from the database.
	 */
	public function clear_transients() {
		global $wpdb;

		$transients = $wpdb->get_col(
			"SELECT `option_name` AS `name`
			FROM  $wpdb->options
			WHERE `option_name` LIKE '%\\_transient\\_rank_math%'
			ORDER BY `option_name`"
		);

		if ( empty( $transients ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No Rank Math transients found.', 'rank-math' ),
			];
		}

		$count = 0;
		foreach ( $transients as $transient ) {
			delete_option( $transient );
			$count++;
		}

		// Translators: placeholder is the number of transients deleted.
		return sprintf( _n( '%d Rank Math transient cleared.', '%d Rank Math transients cleared.', $count, 'rank-math' ), $count );
	}

	/**
	 * Function to reset the SEO Analyzer.
	 */
	public function clear_seo_analysis() {
		$stored = get_option( 'rank_math_seo_analysis_results' );
		if ( empty( $stored ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'SEO Analyzer data has already been cleared.', 'rank-math' ),
			];
		}

		delete_option( 'rank_math_seo_analysis_results' );
		delete_option( 'rank_math_seo_analysis_date' );

		/**
		 * Fires after SEO Analyzer data is cleared.
		 */
		do_action( 'rank_math/tools/clear_seo_analysis' );

		return __( 'SEO Analyzer data successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete all the Internal Links data.
	 */
	public function delete_links() {
		global $wpdb;

		$exists = $wpdb->get_var( "SELECT EXISTS ( SELECT 1 FROM {$wpdb->prefix}rank_math_internal_links )" );
		if ( empty( $exists ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No Internal Links data found.', 'rank-math' ),
			];
		}

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_internal_links" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_internal_meta" );

		return __( 'Internal Links successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete all the 404 log items.
	 */
	public function delete_log() {
		global $wpdb;

		$exists = $wpdb->get_var( "SELECT EXISTS ( SELECT 1 FROM {$wpdb->prefix}rank_math_404_logs )" );
		if ( empty( $exists ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No 404 log data found.', 'rank-math' ),
			];
		}

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_404_logs;" );

		return __( '404 Log successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete all Redirections data.
	 */
	public function delete_redirections() {
		global $wpdb;

		$exists = $wpdb->get_var( "SELECT EXISTS ( SELECT 1 FROM {$wpdb->prefix}rank_math_redirections )" );
		if ( empty( $exists ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No Redirections found.', 'rank-math' ),
			];
		}

		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_redirections;" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_redirections_cache;" );

		return __( 'Redirection rules successfully deleted.', 'rank-math' );
	}

	/**
	 * Re-create Database Tables.
	 *
	 * @return string
	 */
	public function recreate_tables() {
		// Base.
		Installer::create_tables( get_option( 'rank_math_modules', [] ) );

		// ActionScheduler.
		$this->maybe_recreate_actionscheduler_tables();

		// Analytics module.
		if ( Helper::is_module_active( 'analytics' ) ) {
			as_enqueue_async_action(
				'rank_math/analytics/workflow/create_tables',
				[],
				'rank-math'
			);
		}

		return __( 'Table re-creation started. It might take a couple of minutes.', 'rank-math' );
	}

	/**
	 * Recreate ActionScheduler tables if missing.
	 */
	public function maybe_recreate_actionscheduler_tables() {
		global $wpdb;

		if ( Conditional::is_woocommerce_active() ) {
			return;
		}

		if (
			! class_exists( 'ActionScheduler_HybridStore' )
			|| ! class_exists( 'ActionScheduler_StoreSchema' )
			|| ! class_exists( 'ActionScheduler_LoggerSchema' )
		) {
			return;
		}

		$table_list = [
			'actionscheduler_actions',
			'actionscheduler_logs',
			'actionscheduler_groups',
			'actionscheduler_claims',
		];

		$found_tables = $wpdb->get_col( "SHOW TABLES LIKE '{$wpdb->prefix}actionscheduler%'" ); // phpcs:ignore WordPress.DB.PreparedSQL.InterpolatedNotPrepared
		foreach ( $table_list as $table_name ) {
			if ( ! in_array( $wpdb->prefix . $table_name, $found_tables, true ) ) {
				$this->recreate_actionscheduler_tables();
				return;
			}
		}
	}

	/**
	 * Force the data store schema updates.
	 */
	public function recreate_actionscheduler_tables() {
		$store = new \ActionScheduler_HybridStore();
		add_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10, 2 );

		$store_schema  = new \ActionScheduler_StoreSchema();
		$logger_schema = new \ActionScheduler_LoggerSchema();
		$store_schema->register_tables( true );
		$logger_schema->register_tables( true );

		remove_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10 );
	}

	/**
	 * Function to convert Yoast blocks in posts to Rank Math blocks (FAQ & HowTo).
	 *
	 * @return string
	 */
	public function yoast_blocks() {
		$posts = Yoast_Blocks::get()->find_posts();
		if ( empty( $posts['posts'] ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No posts found to convert.', 'rank-math' ),
			];
		}

		Yoast_Blocks::get()->start( $posts['posts'] );

		return __( 'Conversion started. A success message will be shown here once the process completes. You can close this page.', 'rank-math' );
	}

	/**
	 * Function to convert AIOSEO blocks in posts to Rank Math blocks (TOC).
	 *
	 * @return string
	 */
	public function aioseo_blocks() {
		$posts = AIOSEO_Blocks::get()->find_posts();
		if ( empty( $posts['posts'] ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No posts found to convert.', 'rank-math' ),
			];
		}

		AIOSEO_Blocks::get()->start( $posts['posts'] );

		return __( 'Conversion started. A success message will be shown here once the process completes. You can close this page.', 'rank-math' );
	}

	/**
	 * Function to delete old schema data.
	 *
	 * @return string
	 */
	public function delete_old_schema() {
		$meta = Remove_Schema::get()->find();
		if ( empty( $meta ) ) {
			return [
				'status'  => 'error',
				'message' => __( 'No data found to delete.', 'rank-math' ),
			];
		}

		Remove_Schema::get()->start( $meta );

		return __( 'Deletion started. A success message will be shown here once the process completes. You can close this page.', 'rank-math' );
	}

	/**
	 * Add subpage to Status & Tools screen.
	 *
	 * @param array $pages Pages.
	 * @return array       New pages.
	 */
	public function add_tools_page( $pages ) {
		$pages['tools'] = [
			'url'   => 'status',
			'args'  => 'view=tools',
			'cap'   => 'manage_options',
			'title' => __( 'Database Tools', 'rank-math' ),
			'class' => '\\RankMath\\Tools\\Database_Tools',
		];

		return $pages;
	}

	/**
	 * Get tools.
	 *
	 * @return array
	 */
	private function get_tools() {
		$tools = [];

		if ( Helper::is_module_active( 'seo-analysis' ) ) {
			$tools['clear_seo_analysis'] = [
				'title'       => __( 'Flush SEO Analyzer Data', 'rank-math' ),
				'description' => __( "Need a clean slate or not able to run the SEO Analyzer tool? Flushing the analysis data might fix the issue. Flushing SEO Analyzer data is entirely safe and doesn't remove any critical data from your website.", 'rank-math' ),
				'button_text' => __( 'Clear SEO Analyzer', 'rank-math' ),
			];
		}

		$tools['clear_transients'] = [
			'title'       => __( 'Remove Rank Math Transients', 'rank-math' ),
			'description' => __( 'If you see any issue while using Rank Math or one of its options - clearing the Rank Math transients fixes the problem in most cases. Deleting transients does not delete ANY data added using Rank Math.', 'rank-math' ),
			'button_text' => __( 'Remove transients', 'rank-math' ),
		];

		if ( Helper::is_module_active( '404-monitor' ) ) {
			$tools['delete_log'] = [
				'title'        => __( 'Clear 404 Log', 'rank-math' ),
				'description'  => __( 'Is the 404 error log getting out of hand? Use this option to clear ALL 404 logs generated by your website in the Rank Math 404 Monitor.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete the 404 log? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Clear 404 Log', 'rank-math' ),
			];
		}

		$tools['recreate_tables'] = [
			'title'       => __( 'Re-create Missing Database Tables', 'rank-math' ),
			'description' => __( 'Check if required tables exist and create them if not.', 'rank-math' ),
			'button_text' => __( 'Re-create Tables', 'rank-math' ),
		];

		if ( Helper::is_module_active( 'analytics' ) ) {
			$tools['analytics_fix_collations'] = [
				'title'       => __( 'Fix Analytics table collations', 'rank-math' ),
				'description' => __( 'In some cases, the Analytics database tables or columns don\'t match with each other, which can cause database errors. This tool can fix that issue.', 'rank-math' ),
				'button_text' => __( 'Fix Collations', 'rank-math' ),
			];
		}

		$block_posts = Yoast_Blocks::get()->find_posts();
		if ( is_array( $block_posts ) && ! empty( $block_posts['count'] ) ) {
			$tools['yoast_blocks'] = [
				'title'        => __( 'Yoast Block Converter', 'rank-math' ),
				'description'  => __( 'Convert FAQ, HowTo, & Table of Contents Blocks created using Yoast. Use this option to easily move your previous blocks into Rank Math.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to convert Yoast blocks into Rank Math blocks? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Convert Blocks', 'rank-math' ),
			];
		}

		$aio_block_posts = AIOSEO_Blocks::get()->find_posts();
		if ( is_array( $aio_block_posts ) && ! empty( $aio_block_posts['count'] ) ) {
			$tools['aioseo_blocks'] = [
				'title'        => __( 'AIOSEO Block Converter', 'rank-math' ),
				'description'  => __( 'Convert TOC block created using AIOSEO. Use this option to easily move your previous blocks into Rank Math.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to convert AIOSEO blocks into Rank Math blocks? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Convert Blocks', 'rank-math' ),
			];
		}

		if ( Helper::is_module_active( 'link-counter' ) ) {
			$tools['delete_links'] = [
				'title'        => __( 'Delete Internal Links Data', 'rank-math' ),
				'description'  => __( 'In some instances, the internal links data might show an inflated number or no number at all. Deleting the internal links data might fix the issue.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete Internal Links Data? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Delete Internal Links', 'rank-math' ),
			];
		}

		if ( Helper::is_module_active( 'redirections' ) ) {
			$tools['delete_redirections'] = [
				'title'        => __( 'Delete Redirections Rules', 'rank-math' ),
				'description'  => __( 'Getting a redirection loop or need a fresh start? Delete all the redirections using this tool. Note: This process is irreversible and will delete ALL your redirection rules.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete all the Redirection Rules? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Delete Redirections', 'rank-math' ),
			];
		}

		if ( Helper::is_module_active( 'rich-snippet' ) && ! empty( Remove_Schema::get()->find() ) ) {
			$tools['delete_old_schema'] = [
				'title'        => __( 'Delete Old Schema Data', 'rank-math' ),
				'description'  => __( 'Delete the schema data from the old format (<1.0.48). Note: This process is irreversible and will delete all the metadata prefixed with rank_math_snippet.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete the old schema data? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Delete', 'rank-math' ),
			];
		}

		if ( ! empty( Update_Score::get()->find() ) ) {
			$tools['update_seo_score'] = [
				'title'       => __( 'Update SEO Scores', 'rank-math' ),
				'description' => __( 'This tool will calculate the SEO score for the posts/pages that have a Focus Keyword set. Note: This process may take some time and the browser tab must be kept open while it is running.', 'rank-math' ),
				'button_text' => __( 'Recalculate Scores', 'rank-math' ),
			];
		}

		/**
		 * Filters the list of tools available on the Database Tools page.
		 *
		 * @param array $tools The tools.
		 */
		$tools = $this->do_filter( 'database/tools', $tools );

		return $tools;
	}
}
