<?php
/**
 * The SEO Analyzer class runs the tests and handles the results.
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
use RankMath\Helpers\Param;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * SEO_Analyzer class.
 */
class SEO_Analyzer {

	use Ajax;
	use Hooker;

	/**
	 * Rank Math SEO Checkup API.
	 *
	 * @var string
	 */
	private $api_url = '';

	/**
	 * URL to analyze.
	 *
	 * @var string
	 */
	public $analyse_url = '';

	/**
	 * Sub-page URL to analyze.
	 *
	 * @var string
	 */
	public $analyse_subpage = false;

	/**
	 * Hold analysis results.
	 *
	 * @var null|array
	 */
	public $results = null;

	/**
	 * Hold analysis results.
	 *
	 * @var null|array
	 */
	public $results_data = null;

	/**
	 * Hold analysis result date.
	 *
	 * @var mixed
	 */
	public $results_date = null;

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
	 * The constructor.
	 */
	public function __construct() {
		$this->analyse_url = home_url();

		$this->action( 'init', 'set_url' );
		$this->maybe_clear_storage();

		$this->ajax( 'analyze', 'analyze_me' );
		$this->ajax( 'enable_auto_update', 'enable_auto_update' );
	}

	/**
	 * Set URL and other properties on init.
	 *
	 * @return void
	 */
	public function set_url() {
		update_option( 'rank_math_viewed_seo_analyer', true, false ); // Code to update the viewed value to remove the New label.

		$this->api_url = $this->do_filter( 'seo_analysis/api_endpoint', 'https://rankmath.com/analyze/v2/json/' );
		if ( ! empty( $_REQUEST['u'] ) && $this->is_allowed_url( Param::request( 'u' ) ) ) { // phpcs:ignore
			$this->analyse_url     = esc_url_raw( Param::request( 'u' ) );
			$this->analyse_subpage = true;
		}

		/**
		 * Action: 'rank_math/seo_analysis/after_set_url' - Fires after setting the URL.
		 */
		$this->do_action( 'seo_analysis/after_set_url', $this );

		if ( $this->analyse_subpage ) {
			return;
		}

		$this->get_results_from_storage();
		$this->local_tests = $this->do_filter( 'seo_analysis/tests', [] );
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

			++$statuses[ $result->get_status() ];
			++$total;

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
		return ! is_object( $result ) ? false : true;
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
	 * Get result from storage.
	 *
	 * @param string $option Option name.
	 */
	public function get_results_from_storage( $option = 'rank_math_seo_analysis' ) {
		$this->results      = get_option( $option . '_results' );
		$this->results_date = get_option( $option . '_date' );

		$url = get_option( $option . '_url' );
		if ( false !== $url ) {
			$this->analyse_url = $url;
		}

		$this->build_results();
		if ( empty( $this->results ) ) {
			return [];
		}

		return $this->get_results();
	}

	/**
	 * Return formatted date.
	 */
	public function get_last_checked_date() {
		if ( ! $this->results_date ) {
			return;
		}

		$date = date_i18n( get_option( 'date_format' ), $this->results_date );
		$time = date_i18n( get_option( 'time_format' ), $this->results_date );

		return [
			'date' => $date,
			'time' => $time,
		];
	}

	/**
	 * Clear stored results if needed.
	 */
	private function maybe_clear_storage() {
		if ( '1' === Param::request( 'clear_results' ) ) {
			delete_option( 'rank_math_seo_analysis_results' );
			delete_option( 'rank_math_seo_analysis_date' );
			Helper::redirect( Security::remove_query_arg_raw( 'clear_results' ) );
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

		$this->results_data = $this->results;
		$this->results_date = time();
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
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'site_analysis' );

		$this->results = null;
		$success       = true;
		$directory     = __DIR__;

		$this->set_url();
		if ( ! $this->analyse_subpage ) {
			delete_option( 'rank_math_seo_analysis_results' );
			delete_option( 'rank_math_seo_analysis_date' );
		}

		if ( ! $this->run_api_tests() ) {
			$this->error(
				'<div class="notice notice-error is-dismissible notice-seo-analysis-error rank-math-notice">
					<p>' .
						/* translators: API error */
						sprintf( __( '<strong>API Error:</strong> %s', 'rank-math' ), $this->api_error ) .
					'</p>
				</div>'
			);
			$success = false;
			die;
		}

		if ( ! $this->analyse_subpage ) {
			$this->run_local_tests();
			update_option( 'rank_math_seo_analysis_results', $this->results, false );
			update_option( 'rank_math_seo_analysis_date', time(), false );
		}

		/**
		 * Action: 'rank_math/seo_analysis/after_analyze' - Fires after the SEO analysis is done.
		 */
		$this->do_action( 'seo_analysis/after_analyze', $this );
		$this->build_results();

		$this->success( $this->get_results() );
	}

	/**
	 * Ajax handler for the Enable auto update button.
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
	 * Update the auto update value in the stored results.
	 */
	public function enable_auto_update_in_stored_data() {
		$results = get_option( 'rank_math_seo_analysis_results' );
		if ( ! isset( $results['auto_update'] ) ) {
			return;
		}

		$results['auto_update']['status']  = 'ok';
		$results['auto_update']['message'] = __( 'Rank Math auto-update option is enabled on your site.', 'rank-math' );
		update_option( 'rank_math_seo_analysis_results', $results, false );
	}

	/**
	 * Run test through the Rank Math Analysis API.
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
					'fix'         => isset( $test['how_to_fix'] ) ? $test['how_to_fix'] : '',
					'category'    => $test['category'],
					'info'        => [],
					'kb_link'     => isset( $test['kb_link'] ) ? $test['kb_link'] : 'https://rankmath.com/kb/seo-analysis',
					'tooltip'     => ! empty( $test['tooltip'] ) ? $test['tooltip'] : '',
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
		$allowed = true;
		$home    = get_home_url();
		if ( strpos( $url, $home ) !== 0 ) {
			$allowed = false;
		}

		// wp-admin pages are not allowed.
		if ( strpos( substr( $url, strlen( $home ) ), '/wp-admin' ) === 0 ) {
			$allowed = false;
		}

		return $this->do_filter( 'analysis/is_allowed_url', $allowed, $url );
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
			$data[ $category ][ $result->get_id() ] = $result->get_result();
		}

		return $data;
	}

	/**
	 * Get SEO Analysis results.
	 *
	 * @return array
	 */
	private function get_results() {
		return [
			'results'  => $this->sort_results_by_category(),
			'metrices' => $this->get_graph_metrices(),
			'date'     => $this->get_last_checked_date(),
			'serpData' => $this->get_serp_data(),
		];
	}

	/**
	 * Get SERP Data.
	 *
	 * @return array
	 */
	private function get_serp_data() {
		$src_format = 'https://t0.gstatic.com/faviconV2?client=SOCIAL&type=FAVICON&fallback_opts=TYPE,SIZE,URL&url=%%SITEURL%%&size=128';
		$favicon    = str_replace( '%%SITEURL%%', rawurlencode( $this->analyse_url ), $src_format );
		if ( is_array( $this->results ) ) {
			if ( isset( $this->results['title_length'] ) ) {
				$title_data = $this->results['title_length']->get_result();
				$title      = $title_data['data'];
			}

			if ( isset( $this->results['description_length'] ) ) {
				$description_data = $this->results['description_length']->get_result();
				$description      = $description_data['data'];
			}
		}

		if ( empty( $title ) ) {
			$title = __( '(No Title)', 'rank-math' );
		}
		// Cut title to 60 characters.
		if ( strlen( $title ) > 60 ) {
			$title = substr( $title, 0, 60 ) . '...';
		}

		if ( empty( $description ) ) {
			$description = __( '(No Description)', 'rank-math' );
		}
		// Cut description to 160 characters.
		if ( strlen( $description ) > 160 ) {
			$description = substr( $description, 0, 160 ) . '...';
		}

		return [
			'favicon'     => $favicon,
			'url'         => esc_url( $this->analyse_url ),
			'title'       => esc_html( $title ),
			'description' => esc_html( $description ),
		];
	}
}
