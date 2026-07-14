<?php
/**
 * Subscriber for Analytics abilities.
 *
 * @since      1.0.274
 * @package    RankMath
 * @subpackage RankMath\Abilities\Analytics
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Analytics;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Analytics ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Analytics abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-analytics';

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
		$this->action( 'wp_abilities_api_init', 'register_get_top_keywords' );
	}

	/**
	 * Register the Analytics ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Analytics', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to query Google Search Console and analytics data.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-top-keywords ability.
	 *
	 * @return void
	 */
	public function register_get_top_keywords(): void {
		( new Get_Top_Keywords( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
