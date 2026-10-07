<?php
/**
 * Subscriber for Redirection abilities.
 *
 * @since      1.0.278
 * @package    RankMath
 * @subpackage RankMath\Abilities\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Redirections;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Redirection ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Redirection abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-redirections';

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
		$this->action( 'wp_abilities_api_init', 'register_get_redirections' );
	}

	/**
	 * Register the Redirection ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Redirections', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to read and manage redirections for a site.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/get-redirections ability.
	 *
	 * @return void
	 */
	public function register_get_redirections(): void {
		( new Get_Redirections( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
