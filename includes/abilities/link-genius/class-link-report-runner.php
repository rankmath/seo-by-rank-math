<?php
/**
 * Runner for the get-link-report ability.
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Link_Genius
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Link_Genius;

use RankMath\Links\Api\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Calls the existing Links REST controller methods to build a link report.
 */
class Link_Report_Runner {

	/**
	 * Links REST controller.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @param Controller|null $controller Links REST controller.
	 */
	public function __construct( ?Controller $controller = null ) {
		$this->controller = $controller ?? new Controller();
	}

	/**
	 * Build and return the link report.
	 *
	 * @param array $input Ability input (e.g. include_posts).
	 * @return array
	 */
	public function run( array $input = [] ): array {
		$posts_response = $this->controller->get_posts_stats();
		$links_response = $this->controller->get_links_stats();

		$posts = (array) $posts_response->get_data();
		$links = (array) $links_response->get_data();

		$total_posts = (int) ( $posts['total_posts'] ?? 0 );

		$result = [
			'stats' => [
				'total_internal'    => (int) ( $links['internal'] ?? 0 ),
				'total_external'    => (int) ( $links['external'] ?? 0 ),
				'posts_no_internal' => $total_posts - (int) ( $posts['posts_with_internal'] ?? 0 ),
				'posts_no_external' => $total_posts - (int) ( $posts['posts_with_external'] ?? 0 ),
			],
		];

		if ( ! empty( $input['include_posts'] ) ) {
			$data                     = $this->controller->get_posts_data( [ 'per_page' => 100 ] );
			$result['stats']['posts'] = array_map(
				function ( $post ) {
					$row = (array) $post;
					return [
						'post_id'    => (int) ( $row['post_id'] ?? 0 ),
						'post_title' => (string) ( $row['post_title'] ?? '' ),
						'counts'     => [
							'internal' => (int) ( $row['internal_link_count'] ?? 0 ),
							'external' => (int) ( $row['external_link_count'] ?? 0 ),
						],
					];
				},
				$data['posts']
			);
		}

		$result = (array) apply_filters( 'rank_math/abilities/link_report_result', $result, $input );

		if ( ! isset( $result['audit'] ) ) {
			$result['upgrade'] = [
				'message' => \esc_html__( 'Upgrade to Rank Math PRO to unlock broken link detection, redirect chain analysis, and HTTP status distribution from the Link Genius audit.', 'seo-by-rank-math' ),
				'url'     => 'https://rankmath.com/pricing/',
			];
		}

		return $result;
	}
}
