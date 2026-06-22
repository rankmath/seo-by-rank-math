<?php
/**
 * Subscriber for Schema abilities.
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Schema;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Schema ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Schema abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-schema';

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
		$this->action( 'wp_abilities_api_init', 'register_get_post_schema' );
	}

	/**
	 * Register the Schema ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Schema', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to read and manage schema markup attached to posts.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-post-schema ability.
	 *
	 * @return void
	 */
	public function register_get_post_schema(): void {
		( new Get_Post_Schema( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
