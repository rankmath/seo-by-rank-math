<?php
/**
 * Audit runner service for the rank-math/audit-site-seo ability.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

use RankMath\KB;
use RankMath\Helpers\Url;
use RankMath\SEO_Analysis\SEO_Analyzer;

defined( 'ABSPATH' ) || exit;

/**
 * Runs the Rank Math site SEO audit and shapes the result for ability/MCP consumers.
 */
class Audit_Runner {

	/**
	 * SEO Analyzer instance.
	 *
	 * @var SEO_Analyzer
	 */
	private $analyzer;

	/**
	 * Constructor.
	 *
	 * @param SEO_Analyzer|null $analyzer SEO Analyzer instance.
	 */
	public function __construct( ?SEO_Analyzer $analyzer = null ) {
		$this->analyzer = $analyzer ?? new SEO_Analyzer();
	}

	/**
	 * Run the audit and return a structured result.
	 *
	 * External URL auditing requires Rank Math PRO. FREE installs receive
	 * an Audit_Result with error.code 'pro_required' instead of findings.
	 *
	 * @param bool        $refresh Re-run tests instead of returning cached results.
	 * @param string|null $url     Optional URL to audit. Defaults to this site's home URL.
	 * @return Audit_Result
	 */
	public function run( $refresh = true, $url = null ) {
		if ( ! empty( $url ) ) {
			$clean = wp_http_validate_url( $url );
			if ( false === $clean ) {
				return $this->error_result(
					'invalid_url',
					__( 'The provided URL is not valid.', 'seo-by-rank-math' )
				);
			}

			if ( Url::is_external( $clean ) && ! apply_filters( 'rank_math/analysis/is_allowed_url', false, $clean ) ) {
				return $this->error_result(
					'pro_required',
					sprintf(
						/* translators: %s: URL to the Rank Math PRO upgrade page */
						__( 'Competitor URL analysis requires Rank Math PRO. Upgrade at %s', 'seo-by-rank-math' ),
						KB::get( 'pro', 'Audit Site SEO Ability' )
					)
				);
			}
		}

		// The tests file is normally loaded only in the admin context. Include it here
		// so local tests are available when the ability is invoked via REST/MCP.
		$tests_file = RANK_MATH_PATH . 'includes/modules/seo-analysis/seo-analysis-tests.php';
		if ( file_exists( $tests_file ) ) {
			include_once $tests_file;
		}

		if ( ! empty( $url ) ) {
			$this->analyzer->analyse_url     = esc_url_raw( $url );
			$this->analyzer->analyse_subpage = true;
		}

		$this->analyzer->set_url();

		if ( $refresh || ! $this->has_cached_results() ) {
			$this->analyzer->run_tests( $refresh );
		} else {
			$this->analyzer->get_results_from_storage();
		}

		return $this->shape( $this->analyzer );
	}

	/**
	 * Build a minimal Audit_Result carrying only an error (no findings).
	 *
	 * @param string $code    Machine-readable error code.
	 * @param string $message Human-readable message for the AI/user.
	 * @return Audit_Result
	 */
	private function error_result( $code, $message ) {
		return new Audit_Result(
			[
				'url'               => '',
				'score'             => 0,
				'grade'             => 'bad',
				'statuses'          => [
					'ok'      => 0,
					'fail'    => 0,
					'warning' => 0,
					'info'    => 0,
				],
				'total_tests'       => 0,
				'last_run_at'       => 0,
				'remote_api_status' => 'skipped',
				'findings'          => [],
				'error'             => [
					'code'    => $code,
					'message' => $message,
				],
			]
		);
	}

	/**
	 * Whether a cached audit result exists.
	 *
	 * @return bool
	 */
	private function has_cached_results() {
		return (bool) get_option( 'rank_math_seo_analysis_results' );
	}

	/**
	 * Build an Audit_Result from a populated SEO_Analyzer.
	 *
	 * @param SEO_Analyzer $analyzer Populated analyzer instance.
	 * @return Audit_Result
	 */
	private function shape( SEO_Analyzer $analyzer ) {
		$findings   = [];
		$statuses   = [
			'ok'      => 0,
			'fail'    => 0,
			'warning' => 0,
			'info'    => 0,
		];
		$total      = 0;
		$score_sum  = 0;
		$score_pass = 0;

		if ( is_array( $analyzer->results ) ) {
			foreach ( $analyzer->results as $id => $result ) {
				if ( ! is_object( $result ) || $result->is_hidden() || $result->is_excluded() ) {
					continue;
				}

				$data   = $result->get_result();
				$status = $result->get_status();
				$score  = (int) $result->get_score();

				$statuses[ $status ] = isset( $statuses[ $status ] ) ? $statuses[ $status ] + 1 : 1;
				++$total;
				$score_sum += $score;
				if ( 'ok' === $status ) {
					$score_pass += $score;
				}

				// Prefer the per-run `message` (contains actual counts/links) over the
				// static `description` (generic text defined in the test registration).
				$description = isset( $data['message'] ) ? $data['message'] : ( isset( $data['description'] ) ? $data['description'] : '' );

				$fix_html = isset( $data['fix'] ) ? $data['fix'] : '';

				$findings[] = new Finding(
					[
						'test_id'     => $id,
						'category'    => $result->get_category(),
						'status'      => $status,
						'score'       => $score,
						'title'       => isset( $data['title'] ) ? $data['title'] : '',
						'description' => wp_strip_all_tags( $description ),
						'fix_text'    => html_entity_decode( wp_strip_all_tags( $fix_html ), ENT_QUOTES | ENT_HTML5, 'UTF-8' ),
						'fix_html'    => $fix_html,
						'fix_hint'    => Fix_Hint_Map::get( $id ),
						'kb_link'     => isset( $data['kb_link'] ) ? $data['kb_link'] : '',
						'data'        => isset( $data['data'] ) ? $data['data'] : null,
					]
				);
			}
		}

		$percent = $score_sum > 0 ? (int) round( ( $score_pass / $score_sum ) * 100 ) : 0;

		return new Audit_Result(
			[
				'url'               => $analyzer->analyse_url,
				'score'             => $percent,
				'grade'             => $this->grade_for( $percent ),
				'statuses'          => $statuses,
				'total_tests'       => $total,
				'last_run_at'       => (int) get_option( 'rank_math_seo_analysis_date', 0 ),
				'remote_api_status' => $analyzer->remote_api_succeeded() ? 'ok' : 'unavailable',
				'findings'          => $findings,
				'error'             => null,
			]
		);
	}

	/**
	 * Score → grade bucket.
	 *
	 * @param int $percent Score percentage 0–100.
	 * @return string good|average|bad
	 */
	private function grade_for( $percent ) {
		if ( $percent < 50 ) {
			return 'bad';
		}
		if ( $percent < 70 ) {
			return 'average';
		}
		return 'good';
	}
}
