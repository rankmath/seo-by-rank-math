<?php
/**
 * Subscriber for Sitemap abilities.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Sitemap;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Sitemap ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Sitemap abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-sitemap';

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
		$this->action( 'wp_abilities_api_init', 'register_get_sitemap_status' );
	}

	/**
	 * Register the Sitemap ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Sitemap', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to inspect the XML sitemap status.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-sitemap-status ability.
	 *
	 * @return void
	 */
	public function register_get_sitemap_status(): void {
		( new Get_Sitemap_Status( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
