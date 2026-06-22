<?php
/**
 * Subscriber for Link Genius abilities.
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Abilities\Link_Genius
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Link_Genius;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Link Genius ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Link Genius abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-link-genius';

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
		$this->action( 'wp_abilities_api_init', 'register_get_link_report' );
		$this->action( 'wp_abilities_api_init', 'register_get_post_links' );
	}

	/**
	 * Register the Link Genius ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Link Genius', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to retrieve and analyse internal and external link data for posts.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-link-report ability.
	 *
	 * @return void
	 */
	public function register_get_link_report(): void {
		( new Get_Link_Report( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/get-post-links ability.
	 *
	 * @return void
	 */
	public function register_get_post_links(): void {
		( new Get_Post_Links( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
