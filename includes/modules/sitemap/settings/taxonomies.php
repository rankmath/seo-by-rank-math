<?php
/**
 * Sitemap settings - taxonomy tabs.
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$taxonomy   = $tab['taxonomy'];
$prefix     = "tax_{$taxonomy}_";
$is_enabled = 'category' === $taxonomy ? 'on' : 'off';

$cmb->add_field(
	[
		'id'      => $prefix . 'sitemap',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include in Sitemap', 'rank-math' ),
		'desc'    => esc_html__( 'Include archive pages for terms of this taxonomy in the XML sitemap.', 'rank-math' ),
		'default' => $is_enabled,
	]
);

$cmb->add_field(
	[
		'id'      => $prefix . 'html_sitemap',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include in HTML Sitemap', 'rank-math' ),
		'desc'    => esc_html__( 'Include archive pages for terms of this taxonomy in the HTML sitemap.', 'rank-math' ),
		'default' => $is_enabled,
		'classes' => [
			'rank-math-html-sitemap',
			! Helper::get_settings( 'sitemap.html_sitemap' ) ? 'hidden' : ''
		],
	]
);

$cmb->add_field(
	[
		'id'      => $prefix . 'include_empty',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include Empty Terms', 'rank-math' ),
		'desc'    => esc_html__( 'Include archive pages of terms that have no posts associated.', 'rank-math' ),
		'default' => 'off',
		'dep'     => [ [ $prefix . 'sitemap', 'on' ] ],
		'classes' => 'rank-math-advanced-option',
	]
);
