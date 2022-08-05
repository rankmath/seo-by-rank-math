<?php
/**
 * The Web Stories module.
 *
 * @since      1.0.45
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Web_Stories;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Web_Stories class.
 */
class Web_Stories {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->action( 'web_stories_story_head', 'remove_web_stories_meta_tags', 0 );
		$this->action( 'web_stories_story_head', 'add_rank_math_tags' );
		$this->action( 'rank_math/json_ld', 'change_publisher_logo', 99, 2 );
	}

	/**
	 * Remove all meta tags added by the Web Stories plugin.
	 */
	public function remove_web_stories_meta_tags() {
		add_filter( 'web_stories_enable_metadata', '__return_false' );
		add_filter( 'web_stories_enable_schemaorg_metadata', '__return_false' );
		add_filter( 'web_stories_enable_open_graph_metadata', '__return_false' );
		add_filter( 'web_stories_enable_twitter_metadata', '__return_false' );
		remove_action( 'web_stories_story_head', 'rel_canonical' );
	}

	/**
	 * Add Rank Math meta tags.
	 */
	public function add_rank_math_tags() {
		do_action( 'rank_math/head' );
	}

	/**
	 * Change Publisher logo on Web Stories posts.
	 *
	 * @param array  $data    Array of JSON-LD data.
	 * @param JsonLD $json_ld The JsonLD instance.
	 *
	 * @return array
	 */
	public function change_publisher_logo( $data, $json_ld ) {
		if ( ! is_singular( 'web-story' ) || ! $json_ld->can_add_global_entities( $data ) || ! isset( $data['publisher'] ) ) {
			return $data;
		}

		global $post;
		$story = new \Google\Web_Stories\Model\Story();
		$story->load_from_post( $post );

		$url = $story->get_publisher_logo_url();
		if ( ! $url ) {
			return $data;
		}

		$size                      = $story->get_publisher_logo_size();
		$data['publisher']['logo'] = [
			'@type'  => 'ImageObject',
			'@id'    => home_url( '/#logo' ),
			'url'    => $url,
			'width'  => $size[0],
			'height' => $size[1],
		];

		return $data;
	}
}
