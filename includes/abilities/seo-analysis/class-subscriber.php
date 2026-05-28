<?php
/**
 * Subscriber for SEO Analysis abilities.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities\SEO_Analysis
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities\SEO_Analysis;

use RankMath\Traits\Hooker;
use RankMath\Abilities\Subscriber_Interface;

defined( 'ABSPATH' ) || exit;

/**
 * Registers the SEO Analysis ability category and its abilities.
 */
class Subscriber implements Subscriber_Interface {

	use Hooker;

	/**
	 * Ability category slug for SEO Analysis abilities.
	 */
	const CATEGORY_SLUG = 'rank-math-seo-analysis';

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
		$this->action( 'wp_abilities_api_init', 'register_audit_site_seo' );
		$this->action( 'wp_abilities_api_init', 'register_fix_site_seo' );
	}

	/**
	 * Register the SEO Analysis ability category.
	 *
	 * @return void
	 */
	public function register_category(): void {
		\wp_register_ability_category(
			self::CATEGORY_SLUG,
			[
				'label'       => esc_html__( 'SEO Analysis', 'seo-by-rank-math' ),
				'description' => esc_html__( 'Abilities to audit and fix site-wide SEO issues.', 'seo-by-rank-math' ),
			]
		);
	}

	/**
	 * Register the rank-math/audit-site-seo ability.
	 *
	 * @return void
	 */
	public function register_audit_site_seo(): void {
		( new Audit_Site_SEO( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}

	/**
	 * Register the rank-math/fix-site-seo ability.
	 *
	 * @return void
	 */
	public function register_fix_site_seo(): void {
		( new Fix_Site_SEO( self::CATEGORY_SLUG, $this->shared_meta ) )->register();
	}
}
