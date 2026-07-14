<?php
/**
 * Subscriber for Content Analysis abilities.
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Content_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Content_Analysis;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Content Analysis ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Content Analysis abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-content-analysis';

	/**
	 * Shared meta args.
	 *
	 * @var array
	 */
	private $shared_meta;

	/**
	 * Constructor.
	 *
	 * @param array $shared_meta Shared meta args from the top-level Abilities class.
	 */
	public function __construct( array $shared_meta ) {
		$this->shared_meta = $shared_meta;
	}

	/**
	 * Wire hooks.
	 *
	 * @return void
	 */
	public function register(): void {
		$this->action( 'wp_abilities_api_categories_init', 'register_category' );
		$this->action( 'wp_abilities_api_init', 'register_get_seo_scores' );
		$this->action( 'wp_abilities_api_init', 'register_analyze_post_content' );
	}

	/**
	 * Register the Content Analysis ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Content Analysis', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to retrieve on-page SEO analysis scores for posts.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-seo-scores ability.
	 *
	 * @return void
	 */
	public function register_get_seo_scores(): void {
		( new Get_SEO_Scores( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/analyze-post-content ability.
	 *
	 * @return void
	 */
	public function register_analyze_post_content(): void {
		( new Analyze_Post_Content( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
