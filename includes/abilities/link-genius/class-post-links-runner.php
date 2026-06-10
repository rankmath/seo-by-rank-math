<?php
/**
 * Runner: fetches per-post link data via the existing links REST endpoint.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\Link_Genius
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Link_Genius;

use RankMath\Links\Api\Controller;

defined( 'ABSPATH' ) || exit;

/**
 * Delegates to the Links REST controller to retrieve individual link rows for a post.
 *
 * Calls Controller::get_links() directly instead of going through the REST stack
 * so that the request stays in-process. PRO plugin overrides
 * (rank_math/links/rest_links_response) still apply because they live inside
 * the controller callback itself.
 */
class Post_Links_Runner {

	/**
	 * Links REST controller.
	 *
	 * @var Controller
	 */
	private $controller;

	/**
	 * Constructor.
	 *
	 * @param Controller|null $controller Links REST controller (injectable for tests).
	 */
	public function __construct( ?Controller $controller = null ) {
		$this->controller = $controller ?? new Controller();
	}

	/**
	 * Fetch link rows for a post by calling the links controller directly.
	 *
	 * @param array $args {
	 *     Query arguments.
	 *
	 *     @type int    $post_id     Source post ID. Must be > 0.
	 *     @type int    $page        1-based page number.
	 *     @type int    $per_page    Rows per page (max 100).
	 *     @type string $is_internal Filter value: '1' = internal only, '0' = external only, '' = all.
	 * }
	 * @return array { links: array, total: int, pages: int }
	 */
	public function run( array $args ): array {
		$data  = $this->controller->get_links_data( $args );
		$links = [];

		foreach ( $data['links'] ?? [] as $link ) {
			$links[] = [
				'id'             => (int) $link->id,
				'url'            => $link->url,
				'type'           => $link->type,
				'anchor'         => (string) ( $link->anchor_text ?? '' ),
				'dofollow'       => isset( $link->is_nofollow ) ? ! (bool) $link->is_nofollow : null,
				'target_post_id' => (int) ( $link->target_post_id ?? 0 ),
				'target_title'   => $link->target_title ?? '',
				'target_url'     => $link->target_url ?? '',
			];
		}

		return [
			'links' => $links,
			'total' => (int) ( $data['total'] ?? 0 ),
			'pages' => (int) ( $data['pages'] ?? 0 ),
		];
	}
}
