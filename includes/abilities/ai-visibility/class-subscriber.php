<?php
/**
 * Subscriber for AI Visibility abilities.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\AI_Visibility
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\AI_Visibility;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the AI Visibility ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for AI Visibility abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-ai-visibility';

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
		$this->action( 'wp_abilities_api_init', 'register_get_ai_visibility_overview' );
		$this->action( 'wp_abilities_api_init', 'register_get_ai_visibility_brand_insights' );
		$this->action( 'wp_abilities_api_init', 'register_get_ai_visibility_brand_queries' );
		$this->action( 'wp_abilities_api_init', 'register_create_ai_visibility_brand' );
	}

	/**
	 * Register the AI Visibility ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'AI Visibility', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to retrieve AI brand visibility data tracked by Rank Math.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-ai-visibility-overview ability.
	 *
	 * @return void
	 */
	public function register_get_ai_visibility_overview(): void {
		( new Get_AI_Visibility_Overview( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/get-ai-visibility-brand-insights ability.
	 *
	 * @return void
	 */
	public function register_get_ai_visibility_brand_insights(): void {
		( new Get_AI_Visibility_Brand_Insights( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/get-ai-visibility-brand-queries ability.
	 *
	 * @return void
	 */
	public function register_get_ai_visibility_brand_queries(): void {
		( new Get_AI_Visibility_Brand_Queries( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/create-ai-visibility-brand ability.
	 *
	 * @return void
	 */
	public function register_create_ai_visibility_brand(): void {
		( new Create_AI_Visibility_Brand( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
