<?php
/**
 * Subscriber for Settings abilities.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Abilities\Settings
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\Settings;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the Settings ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for Settings abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-settings';

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
		$this->action( 'wp_abilities_api_init', 'register_abilities' );
	}

	/**
	 * Register the Settings ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'Settings', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to read and configure Rank Math plugin settings.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register all Settings abilities.
	 *
	 * @return void
	 */
	public function register_abilities(): void {
		$classes = [
			Get_Settings::class,
			Get_System_Status::class,
			Set_Website_Identity::class,
			Set_Global_Seo_Settings::class,
			Set_Homepage_Seo::class,
			Set_Link_Settings::class,
			Set_Module_Status::class,
			Set_Sitemap_Settings::class,
			Set_Plugin_Preferences::class,
			Set_Post_Type_Seo_Settings::class,
			Set_Breadcrumb_Settings::class,
		];

		foreach ( $classes as $class ) {
			( new $class( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
		}
	}
}
