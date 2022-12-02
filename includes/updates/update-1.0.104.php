<?php
/**
 * The Updates routine for version 1.0.104
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Disable HTML sitemap by default, but copy the posts/terms "include in
 * sitemap" settings to the new HTML sitemap settings.
 */
function rank_math_1_0_104_update_html_sitemap() {
	$all_opts = rank_math()->settings->all_raw();

	$sitemap_settings                    = $all_opts['sitemap'];
	$sitemap_settings['html_sitemap']    = 'off';
	$sitemap_settings['authors_sitemap'] = 'on';

	foreach ( Helper::get_accessible_post_types() as $post_type ) {
		$sitemap_settings[ 'pt_' . $post_type . '_html_sitemap' ] = isset( $sitemap_settings[ 'pt_' . $post_type . '_sitemap' ] ) ? $sitemap_settings[ 'pt_' . $post_type . '_sitemap' ] : 'off';
	}

	foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
		$sitemap_settings[ 'tax_' . $taxonomy . '_html_sitemap' ] = isset( $sitemap_settings[ 'tax_' . $taxonomy . '_sitemap' ] ) ? $sitemap_settings[ 'tax_' . $taxonomy . '_sitemap' ] : 'off';
	}

	Helper::update_all_settings( null, null, $sitemap_settings );
	rank_math()->settings->reset();
}
rank_math_1_0_104_update_html_sitemap();
