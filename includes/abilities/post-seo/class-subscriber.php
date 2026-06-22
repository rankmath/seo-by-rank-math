<?php
/**
 * Subscriber for Post SEO abilities.
 *
 * @since      1.0.272
 * @package    RankMath
 * @subpackage RankMath\Abilities\Post_SEO
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Post_SEO;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Post SEO ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Post SEO abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-post-seo';

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
		$this->action( 'wp_abilities_api_init', 'register_get_post_seo_meta' );
	}

	/**
	 * Register the Post SEO ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Post SEO', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to read and manage per-post SEO metadata.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-post-seo-meta ability.
	 *
	 * @return void
	 */
	public function register_get_post_seo_meta(): void {
		( new Get_Post_SEO_Meta( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
