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
	}

	/**
	 * Remove all meta tags added by the Web Stories plugin.
	 */
	public function remove_web_stories_meta_tags() {
		$instance = \Google\Web_Stories\get_plugin_instance()->discovery;
		remove_action( 'web_stories_story_head', [ $instance, 'print_metadata' ] );
		remove_action( 'web_stories_story_head', [ $instance, 'print_schemaorg_metadata' ] );
		remove_action( 'web_stories_story_head', [ $instance, 'print_open_graph_metadata' ] );
		remove_action( 'web_stories_story_head', [ $instance, 'print_twitter_metadata' ] );
		remove_action( 'web_stories_story_head', 'rel_canonical' );
	}

	/**
	 * Add Rank Math meta tags.
	 */
	public function add_rank_math_tags() {
		add_filter( 'rank_math/frontend/description', '__return_false' );
		add_filter( 'rank_math/opengraph/facebook/og_description', '__return_false' );
		add_filter( 'rank_math/opengraph/twitter/twitter_description', '__return_false' );
		add_filter( 'rank_math/json_ld/breadcrumbs_enabled', '__return_false' );
		do_action( 'rank_math/head' );
	}
}
