<?php
/**
 * The Database Tools Class.
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
		Schema_Converter::get();
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
	 * Function to clear all the transients.
	 */
	public function clear_transients() {
		global $wpdb;

		$transients = $wpdb->get_col(
			"SELECT `option_name` AS `name`
			FROM  $wpdb->options
			WHERE `option_name` LIKE '%_transient_rank_math%'
			ORDER BY `option_name`"
		);

		if ( empty( $transients ) ) {
			return;
		}

		foreach ( $transients as $transient ) {
			delete_option( $transient );
		}

		return __( 'Rank Math transients cleared.', 'rank-math' );
	}

	/**
	 * Function to reset SEO Analysis.
	 */
	public function clear_seo_analysis() {
		delete_option( 'rank_math_seo_analysis_results' );

		return __( 'SEO Analysis data successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete the Internal Links data.
	 */
	public function delete_links() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_internal_links;" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_internal_meta;" );

		return __( 'Internal Links successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete 404 log.
	 */
	public function delete_log() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_404_logs;" );

		return __( '404 Log successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to delete the Redirections data.
	 */
	public function delete_redirections() {
		global $wpdb;
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_redirections;" );
		$wpdb->query( "TRUNCATE TABLE {$wpdb->prefix}rank_math_redirections_cache;" );

		return __( 'Redirection rules successfully deleted.', 'rank-math' );
	}

	/**
	 * Function to convert the Review schema type.
	 *
	 * @return string
	 */
	public function convert_review() {
		$posts = Helper::get_review_posts();
		if ( empty( $posts ) ) {
			return __( 'No review posts found.', 'rank-math' );
		}

		$count = 0;
		foreach ( $posts as $post_id ) {
			update_post_meta( $post_id, 'rank_math_rich_snippet', $this->do_filter( 'convert_review/type', 'article', $post_id ) );
			update_post_meta( $post_id, 'rank_math_snippet_article_type', $this->do_filter( 'convert_review/article_type', 'BlogPosting', $post_id ) );
			$count++;
		}

		update_option( 'rank_math_review_posts_converted', true );

		/* translators: Number of posts updated */
		return sprintf( __( '%1$d review Posts updated. You can find the list of all converted posts %2$s.', 'rank-math' ), $count, '<a href="' . esc_url( admin_url( 'edit.php?post_type=post&review_posts=1' ) ) . '" target="_blank">' . esc_attr__( 'here', 'rank-math' ) . '</a>' );
	}

	/**
	 * Schema Converter
	 *
	 * @return string
	 */
	public function convert_schema() {
		$posts = Schema_Converter::get()->find_posts();
		if ( empty( $posts['posts'] ) ) {
			return esc_html__( 'No posts found to convert.', 'rank-math' );
		}

		Schema_Converter::get()->start( $posts['posts'] );

		return __( 'Conversion started. A success message will be shown here once the process completes. You can close this page.', 'rank-math' );
	}

	/**
	 * Re-create Database Tables.
	 *
	 * @return string
	 */
	public function recreate_tables() {
		// Base.
		Installer::create_tables();

		// ActionScheduler.
		$this->maybe_recreate_actionscheduler_tables();

		// Analytics module.
		$is_connected = ( get_option( 'rank_math_google_analytic_profile', [] ) !== [] );
		if ( Helper::is_module_active( 'analytics' ) && $is_connected ) {
			delete_option( 'rank_math_analytics_installed' );
			( new \RankMath\Analytics\Installer() )->install( false );

			if ( defined( 'RANK_MATH_PRO_VERSION' ) ) {
				delete_option( 'rank_math_analytics_pro_installed' );
				( new \RankMathPro\Analytics\Installer() )->install();
			}
		}

		return __( 'Tables re-created.', 'rank-math' );
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
	function recreate_actionscheduler_tables() {
		$store = new \ActionScheduler_HybridStore();
		add_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10, 2 );

		$store_schema  = new \ActionScheduler_StoreSchema();
		$logger_schema = new \ActionScheduler_LoggerSchema();
		$store_schema->register_tables( true );
		$logger_schema->register_tables( true );

		remove_action( 'action_scheduler/created_table', [ $store, 'set_autoincrement' ], 10 );
	}

	/**
	 * Yoast Block Converter
	 *
	 * @return string
	 */
	public function yoast_blocks() {
		$posts = Yoast_Blocks::get()->find_posts();
		if ( empty( $posts['posts'] ) ) {
			return esc_html__( 'No posts found to convert.', 'rank-math' );
		}

		Yoast_Blocks::get()->start( $posts['posts'] );

		return __( 'Conversion started. A success message will be shown here once the process completes. You can close this page.', 'rank-math' );
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
		$tools = [
			'clear_seo_analysis'  => [
				'title'       => __( 'Flush SEO Analysis Data', 'rank-math' ),
				'description' => __( "Need a clean slate or not able to run the SEO Analysis tool? Flushing the analysis data might fix the issue. Flushing SEO Analysis data is entirely safe and doesn't remove any critical data from your website.", 'rank-math' ),
				'button_text' => __( 'Clear SEO Analysis', 'rank-math' ),
			],
			'clear_transients'    => [
				'title'       => __( 'Remove Rank Math Transients', 'rank-math' ),
				'description' => __( 'If you see any issue while using Rank Math or one of its options - clearing the Rank Math transients fixes the problem in most cases. Deleting transients does not delete ANY data added using Rank Math.', 'rank-math' ),
				'button_text' => __( 'Remove transients', 'rank-math' ),
			],
			'delete_log'          => [
				'title'        => __( 'Clear 404 Log', 'rank-math' ),
				'description'  => __( 'Is the 404 error log getting out of hand? Use this option to clear ALL 404 logs generated by your website in the Rank Math 404 Monitor.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete the 404 log? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Clear 404 Log', 'rank-math' ),
			],
			'recreate_tables' => [
				'title'       => __( 'Re-create Missing Database Tables', 'rank-math' ),
				/* translators: 1. Review Schema documentation link */
				'description' => __( 'Check if required tables exist and create them if not.', 'rank-math' ),
				'button_text' => __( 'Re-create Tables', 'rank-math' ),
			],
			'convert_schema'      => [
				'title'        => __( 'Schema Converter', 'rank-math' ),
				'description'  => __( 'If you are using v1.0.48 or higher, and if some of the Schema data from previous versions is missing, then use this tool. Please note: Use this tool only if our support staff asked you to run it.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to do this? This is not recommended until and unless you are facing any issues with the revamped Schema Generator.', 'rank-math' ),
				'button_text'  => __( 'Convert Schema', 'rank-math' ),
			],
			'yoast_blocks'        => [
				'title'        => __( 'Yoast Block Converter', 'rank-math' ),
				'description'  => __( 'Convert FAQ & HowTo Blocks created using Yoast. Use this option to easily move your previous blocks into Rank Math.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to convert Yoast blocks into Rank Math blocks? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Convert Blocks', 'rank-math' ),
			],
			'delete_links'        => [
				'title'        => __( 'Delete Internal Links Data', 'rank-math' ),
				'description'  => __( 'In some instances, the internal links data might show an inflated number or no number at all. Deleting the internal links data might fix the issue.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete Internal Links Data? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Delete Internal Links', 'rank-math' ),
			],
			'delete_redirections' => [
				'title'        => __( 'Delete Redirections Rules', 'rank-math' ),
				'description'  => __( 'Getting a redirection loop or need a fresh start? Delete all the redirections using this tool. Note: This process is irreversible and will delete ALL your redirection rules.', 'rank-math' ),
				'confirm_text' => __( 'Are you sure you want to delete all the Redirection Rules? This action is irreversible.', 'rank-math' ),
				'button_text'  => __( 'Delete Redirections', 'rank-math' ),
			],
		];

		if ( ! empty( Helper::get_review_posts() ) ) {
			$tools['convert_review'] = [
				'title'        => __( 'Convert Review Schema into Article', 'rank-math' ),
				/* translators: 1. Review Schema documentation link */
				'description'  => sprintf( __( 'Before using this converter, please read our Knowledge Base Article from %s.', 'rank-math' ), '<a href="https://rankmath.com/kb/how-to-fix-review-schema-errors/" target="_blank">' . esc_attr__( 'here', 'rank-math' ) . '</a>' ),
				/* translators: Number of posts to update */
				'confirm_text' => sprintf( __( 'Are you sure you want to convert %d posts with review schema into new schema type? This action is irreversible.', 'rank-math' ), count( Helper::get_review_posts() ) ),
				'button_text'  => __( 'Convert', 'rank-math' ),
			];
		}

		return $this->do_filter( 'database/tools', $tools );
	}
}
