<?php
/**
 * Sitemap - Taxonomies
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

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
		'id'      => $prefix . 'include_empty',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include Empty Terms', 'rank-math' ),
		'desc'    => esc_html__( 'Include archive pages of terms that have no posts associated.', 'rank-math' ),
		'default' => 'off',
		'dep'     => array( array( $prefix . 'sitemap', 'on' ) ),
		'classes' => 'rank-math-advanced-option',
	]
);
