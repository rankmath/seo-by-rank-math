<?php
/**
 * Subscriber for 404 Monitor abilities.
 *
 * @since      1.0.279
 * @package    RankMath
 * @subpackage RankMath\Abilities\Monitor_404
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Monitor_404;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the 404 Monitor ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for 404 Monitor abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-404-monitor';

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
		$this->action( 'wp_abilities_api_init', 'register_get_404_logs' );
	}

	/**
	 * Register the 404 Monitor ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( '404 Monitor', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to inspect the 404 error monitor log.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-404-logs ability.
	 *
	 * @return void
	 */
	public function register_get_404_logs(): void {
		( new Get_404_Logs( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
