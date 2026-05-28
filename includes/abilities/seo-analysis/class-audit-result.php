<?php
/**
 * Audit result value object for the rank-math/audit-site-seo ability.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

defined( 'ABSPATH' ) || exit;

/**
 * Site SEO audit result, agent-consumable shape.
 */
class Audit_Result {

	/**
	 * Analyzed URL.
	 *
	 * @var string
	 */
	public $url;

	/**
	 * Score 0–100.
	 *
	 * @var int
	 */
	public $score;

	/**
	 * Grade: good|average|bad.
	 *
	 * @var string
	 */
	public $grade;

	/**
	 * Outcome counts: ok, fail, warning, info.
	 *
	 * @var array
	 */
	public $statuses;

	/**
	 * Total tests counted.
	 *
	 * @var int
	 */
	public $total_tests;

	/**
	 * Unix timestamp of last run, 0 if never.
	 *
	 * @var int
	 */
	public $last_run_at;

	/**
	 * Remote API status: ok|unavailable|skipped.
	 *
	 * @var string
	 */
	public $remote_api_status;

	/**
	 * Per-test findings.
	 *
	 * @var Finding[]
	 */
	public $findings;

	/**
	 * Error details when the audit could not run, or null on success.
	 * Shape: { code: string, message: string }
	 *
	 * @var array|null
	 */
	public $error;

	/**
	 * Constructor.
	 *
	 * @param array $args See properties.
	 */
	public function __construct( array $args ) {
		foreach ( $args as $key => $value ) {
			if ( property_exists( $this, $key ) ) {
				$this->{$key} = $value;
			}
		}
	}

	/**
	 * Serialize to array.
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'url'               => $this->url,
			'score'             => (int) $this->score,
			'grade'             => $this->grade,
			'statuses'          => (array) $this->statuses,
			'total_tests'       => (int) $this->total_tests,
			'last_run_at'       => (int) $this->last_run_at,
			'remote_api_status' => $this->remote_api_status,
			'findings'          => array_map(
				function ( Finding $f ) {
					return $f->to_array();
				},
				(array) $this->findings
			),
			'error'             => $this->error,
		];
	}
}
