<?php
/**
 * The public-facing template tags.
 *
 * @package    RankMath
 * @subpackage RankMath\Frontend
 */

use RankMath\Sitemap\Router;
use RankMath\Frontend\Breadcrumbs;
use RankMath\Sitemap\Sitemap;

defined( 'ABSPATH' ) || exit;

/**
 * Is Gutenberg enabled.
 *
 * @return bool
 */
function rank_math_is_gutenberg() {
	return apply_filters( 'rank_math/gutenberg/enabled', true );
}

/**
 * Get breadcrumbs.
 *
 * @param array $args Array of arguments.
 * @return string Breadcrumbs HTML output
 */
function rank_math_get_breadcrumbs( $args = [] ) {
	return Breadcrumbs::get() ? Breadcrumbs::get()->get_breadcrumb( $args ) : '';
}

/**
 * Output breadcrumbs.
 *
 * @param array $args Array of arguments.
 */
function rank_math_the_breadcrumbs( $args = [] ) {
	echo wp_kses_post( rank_math_get_breadcrumbs( $args ) );
}

/**
 * Get sitemap url.
 *
 * @return string
 */
function rank_math_get_sitemap_url() {
	return Router::get_base_url( Sitemap::get_sitemap_index_slug() . '.xml' );
}

/**
 * Get SEO score for a post.
 *
 * @param array $args Array of arguments.
 * @return string Breadcrumbs HTML output
 */
function rank_math_get_seo_score( $args = [] ) {
	return rank_math()->frontend_seo_score->get_output( $args );
}

/**
 * Output SEO score for a post.
 *
 * @param array $args Array of arguments.
 */
function rank_math_the_seo_score( $args = [] ) {
	echo wp_kses_post( rank_math_get_seo_score( $args ) );
}

/**
 * Register extra %variables%. For developers.
 * See rank_math_register_var_replacement().
 *
 * @codeCoverageIgnore
 *
 * @param  string $var       Variable name, for example %custom%. The '%' signs are optional.
 * @param  array  $args      Array with additional title, description and example values for the variable.
 * @param  mixed  $callback  Replacement callback. Should return value and not output it.
 *
 * @return bool Replacement was registered successfully or not.
 */
function rank_math_register_var_replacement( $var, $args = [], $callback = false ) {
	return rank_math()->variables->register_replacement( $var, $args, $callback );
}
