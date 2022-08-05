<?php
/**
 * The Schema Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Schema class.
 */
class Schema {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin();
		}
		$this->action( 'wp', 'integrations' );
		$this->filter( 'rank_math/elementor/dark_styles', 'add_dark_style' );

		new Blocks();
		new Snippet_Shortcode();
	}

	/**
	 * Add dark style
	 *
	 * @param array $styles The dark mode styles.
	 */
	public function add_dark_style( $styles = [] ) {
		$styles['rank-math-schema-dark'] = rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema-dark.css';

		return $styles;
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		$type = get_query_var( 'sitemap' );
		if ( ! empty( $type ) ) {
			return;
		}

		( new JsonLD() )->setup();
	}
}
