<?php
/**
 * The SEO Analyzer
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\SEO_Analysis;

use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Security;
use MyThemeShop\Helpers\Param;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Analyzer class.
 */
class SEO_Analyzer {

	use Ajax, Hooker;

	/**
	 * Rank Math SEO Checkup API.
	 *
	 * @var string
	 */
	private $api_url = '';

	/**
	 * Url to analyze.
	 *
	 * @var string
	 */
	public $analyse_url = '';

	/**
	 * Sub-page url to analyze.
	 *
	 * @var string
	 */
	public $analyse_subpage = false;

	/**
	 * Hold analysis results.
	 *
	 * @var array
	 */
	public $results = [];

	/**
	 * Hold any api error.
	 *
	 * @var array
	 */
	private $api_error = '';

	/**
	 * Hold local test data.
	 *
	 * @var array
	 */
	private $local_tests = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->api_url     = $this->do_filter( 'seo_analysis/api_endpoint', 'https://rankmath.com/analyze/v2/json/' );
		$this->analyse_url = get_home_url();

		if ( ! empty( $_REQUEST['u'] ) && $this->is_allowed_url( Param::request( 'u' ) ) ) {
			$this->analyse_url     = Param::request( 'u' );
			$this->analyse_subpage = true;
		}

		$this->maybe_clear_storage();

		if ( ! $this->analyse_subpage ) {
			$this->get_results_from_storage();
			$this->local_tests = $this->do_filter( 'seo_analysis/tests', [] );
		}

		$this->ajax( 'analyze', 'analyze_me' );
		$this->ajax( 'enable_auto_update', 'enable_auto_update' );
	}

	/**
	 * Output results.
	 */
	public function display() {
		if ( empty( $this->results ) ) {
			return;
		}

		if ( count( $this->results ) < 30 ) {
			return;
		}

		$this->display_graphs();
		?>
		<div class="rank-math-result-tables">
		<?php $this->display_results(); ?>
		</div>
		<?php
	}

	/**
	 * Output graphs
	 */
	private function display_graphs() {
		$data = $this->get_graph_metrices();
		extract( $data ); // phpcs:ignore
		$max = max( $statuses['ok'], $statuses['warning'], $statuses['fail'] );

		include dirname( __FILE__ ) . '/views/graphs.php';
	}

	/**
	 * Get graph metrices.
	 *
	 * @return array
	 */
	private function get_graph_metrices() {
		$total       = 0;
		$percent     = 0;
		$total_score = 0;
		$statuses    = [
			'ok'      => 0,
			'fail'    => 0,
			'info'    => 0,
			'warning' => 0,
		];
		foreach ( $this->results as $id => $result ) {
			if ( false === $this->can_count_result( $result ) ) {
				continue;
			}

			if ( $result->is_hidden() ) {
				continue;
			}

			$statuses[ $result->get_status() ]++;
			$total++;

			$total_score = $total_score + $result->get_score();

			if ( 'ok' !== $result->get_status() ) {
				continue;
			}

			$percent = $percent + $result->get_score();
		}

		$percent = round( ( $percent / $total_score ) * 100 );
		$grade   = $this->get_graph_grade( $percent );

		return compact( 'total', 'percent', 'statuses', 'grade' );
	}

	/**
	 * Can count result.
	 *
	 * @param Result $result Result instance.
	 *
	 * @return bool
	 */
	private function can_count_result( $result ) {
		return ( ! is_object( $result ) || 'info' === $result->get_status() || $result->is_excluded() ) ? false : true;
	}

	/**
	 * Format grade result.
	 *
	 * @param int $percent Total percentage.
	 *
	 * @return string
	 */
	private function get_graph_grade( $percent ) {
		if ( $percent < 70 ) {
			return 'average';
		}

		if ( $percent < 50 ) {
			return 'bad';
		}

		return 'good';
	}

	/**
	 * Output results in tables.
	 */
	private function display_results() {
		foreach ( $this->sort_results_by_category() as $category => $results ) :
			$label = $this->get_category_label( $category );
			?>
			<div class="rank-math-result-table rank-math-result-category-<?php echo esc_attr( $category ); ?>">
				<div class="category-title">
					<?php echo $label; // phpcs:ignore ?>
				</div>
				<?php foreach ( $results as $result ) : ?>
				<div class="table-row">
					<?php echo $result; // phpcs:ignore ?>
				</div>
				<?php endforeach; ?>
			</div>
			<?php
		endforeach;
	}

	/**
	 * Get result from storage.
	 */
	private function get_results_from_storage() {
		$this->results = get_option( 'rank_math_seo_analysis_results' );
		$this->build_results();
	}

	/**
	 * Clear stored results if needed.
	 */
	private function maybe_clear_storage() {
		if ( '1' === Param::request( 'clear_results' ) ) {
			delete_option( 'rank_math_seo_analysis_results' );
			wp_safe_redirect( Security::remove_query_arg_raw( 'clear_results' ) );
			exit;
		}
	}

	/**
	 * Convert result into object.
	 */
	private function build_results() {
		if ( ! is_array( $this->results ) ) {
			return;
		}

		$this->move_priority_results_to_top();

		foreach ( $this->results as $id => $result ) {
			$this->results[ $id ] = new Result( $id, $result, $this->analyse_subpage );
		}
	}

	/**
	 * Move all tests in "priority" category to top.
	 */
	private function move_priority_results_to_top() {
		$priority = [];
		foreach ( $this->results as $id => $result ) {
			if ( is_array( $result ) && 'priority' === $result['category'] ) {
				$priority[ $id ] = $result;
				unset( $this->results[ $id ] );
			}
		}

		$this->results = array_replace( $priority, $this->results );
	}

	/**
	 * Analyze page.
	 */
	public function analyze_me() {
		$success   = true;
		$directory = dirname( __FILE__ );
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'site_analysis' );
		delete_option( 'rank_math_seo_analysis_results' );

		if ( ! $this->run_api_tests() ) {
			/* translators: API error */
			echo '<div class="notice notice-error is-dismissible notice-seo-analysis-error rank-math-notice"><p>' . sprintf( __( '<strong>API Error:</strong> %s', 'rank-math' ), $this->api_error  ) . '</p></div>'; // phpcs:ignore
			$success = false;
			die;
		}

		if ( ! $this->analyse_subpage ) {
			$this->run_local_tests();
			update_option( 'rank_math_seo_analysis_results', $this->results );
		}

		$this->build_results();
		$this->display();

		die;
	}

	/**
	 * Get page score.
	 *
	 * @param  string $url Url to get score for.
	 *
	 * @return int
	 */
	public function get_page_score( $url ) {
		$this->analyse_url     = $url;
		$this->analyse_subpage = true;
		if ( ! $this->run_api_tests() ) {
			error_log( $this->api_error ); // phpcs:ignore
			return 0;
		}

		$this->build_results();

		if ( empty( $this->results ) ) {
			return 0;
		}

		$total = 0;
		foreach ( $this->results as $id => $result ) {
			if (
				$result->is_hidden() ||
				'ok' !== $result->get_status() ||
				false === $this->can_count_result( $result )
			) {
				continue;
			}

			$total = $total + $result->get_score();
		}

		return $total;
	}

	/**
	 * Enable auto update ajax handler.
	 */
	public function enable_auto_update() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'general' );

		$this->enable_auto_update_in_stored_data();
		Helper::toggle_auto_update_setting( 'on' );

		echo '1';
		die;
	}

	/**
	 * Edit SEO analysis stored data.
	 */
	public function enable_auto_update_in_stored_data() {
		$results = get_option( 'rank_math_seo_analysis_results' );
		if ( ! isset( $results['auto_update'] ) ) {
			return;
		}

		$results['auto_update']['status']  = 'ok';
		$results['auto_update']['message'] = __( 'Rank Math auto-update option is enabled on your site.', 'rank-math' );
		update_option( 'rank_math_seo_analysis_results', $results );
	}

	/**
	 * Run test through rank math api.
	 *
	 * @return boolean
	 */
	private function run_api_tests() {
		$response = $this->get_api_results();
		if ( false === $response ) {
			return false;
		}

		$this->process_api_results( $response );

		return true;
	}

	/**
	 * Process results as needed.
	 *
	 * @param array $response Response.
	 *
	 * @return boolean
	 */
	private function process_api_results( $response ) {
		foreach ( $response as $id => $results ) {
			$this->results[ $id ] = wp_parse_args(
				$results,
				[
					'test_id'  => $id,
					'api_test' => true,
				]
			);
		}

		return true;
	}

	/**
	 * Get API results.
	 *
	 * @return bool|array
	 */
	private function get_api_results() {
		$api_url = Security::add_query_arg_raw(
			[
				'u'          => $this->analyse_url,
				'locale'     => get_locale(),
				'is_subpage' => $this->analyse_subpage,
			],
			$this->api_url
		);

		$request = wp_remote_get( $api_url, [ 'timeout' => 30 ] );
		if ( is_wp_error( $request ) ) {
			$this->api_error = wp_strip_all_tags( $request->get_error_message() );
			return false;
		}

		$status = absint( wp_remote_retrieve_response_code( $request ) );
		if ( 200 !== $status ) {
			// Translators: placeholder is a HTTP error code.
			$this->api_error = sprintf( __( 'HTTP %d error.', 'rank-math' ), $status );
			return false;
		}

		$response = wp_remote_retrieve_body( $request );
		$response = json_decode( $response, true );
		if ( ! is_array( $response ) ) {
			$this->api_error = __( 'Unexpected API response.', 'rank-math' );
			return false;
		}

		return $response;
	}

	/**
	 * Run local site tests.
	 */
	private function run_local_tests() {
		foreach ( $this->local_tests as $id => $test ) {
			$this->results[ $id ] = array_merge(
				[
					'test_id'     => $id,
					'api_test'    => false,
					'title'       => $test['title'],
					'description' => $test['description'],
					'how_to_fix'  => isset( $test['how_to_fix'] ) ? $test['how_to_fix'] : '',
					'category'    => $test['category'],
					'info'        => [],
				],
				call_user_func( $test['callback'], $this )
			);
		}
	}

	/**
	 * Check if it is a valid URL on this site.
	 *
	 * @param string $url Check url if it is allowed.
	 * @return bool
	 */
	private function is_allowed_url( $url ) {
		$home = get_home_url();
		if ( strpos( $url, $home ) !== 0 ) {
			return false;
		}

		// wp-admin pages are not allowed.
		if ( strpos( substr( $url, strlen( $home ) ), '/wp-admin' ) === 0 ) {
			return false;
		}

		return true;
	}

	/**
	 * Sort results by category.
	 *
	 * @return array
	 */
	private function sort_results_by_category() {
		$data = [];
		foreach ( $this->results as $result ) {
			if ( $result->is_hidden() ) {
				continue;
			}
			$category = $result->get_category();
			if ( ! isset( $data[ $category ] ) ) {
				$data[ $category ] = [];
			}
			$data[ $category ][ $result->get_id() ] = $result;
		}

		return $data;
	}

	/**
	 * Get category label by slug.
	 *
	 * @param  string $category Current category slug.
	 * @return string
	 */
	private function get_category_label( $category ) {
		$category_map = [
			'priority'    => esc_html__( 'Priority', 'rank-math' ),
			'advanced'    => esc_html__( 'Advanced SEO', 'rank-math' ),
			'basic'       => esc_html__( 'Basic SEO', 'rank-math' ),
			'performance' => esc_html__( 'Performance', 'rank-math' ),
			'security'    => esc_html__( 'Security', 'rank-math' ),
		];

		return isset( $category_map[ $category ] ) ? $category_map[ $category ] : '';
	}
}
