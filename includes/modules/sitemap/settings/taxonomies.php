<?php
/**
 * Sitemap settings - taxonomy tabs.
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

defined( 'ABSPATH' ) || exit;

$current_taxonomy = $tab['taxonomy'];
$prefix           = "tax_{$current_taxonomy}_";
$is_enabled       = 'category' === $current_taxonomy ? 'on' : 'off';

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
		'id'      => $prefix . 'include_empty',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include Empty Terms', 'rank-math' ),
		'desc'    => esc_html__( 'Include archive pages of terms that have no posts associated.', 'rank-math' ),
		'default' => 'off',
		'dep'     => [ [ $prefix . 'sitemap', 'on' ] ],
		'classes' => 'rank-math-advanced-option',
	]
);
