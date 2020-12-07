<?php
/**
 * WooCommerce general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\WooCommerce
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'wc_remove_product_base',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Remove base', 'rank-math' ),
		'desc'    => sprintf(
			/* translators: 1. Example text 2. Example text */
			esc_html__( 'Remove prefix like %1$s from product URL chosen at %2$s', 'rank-math' ),
			'<code>/shop/*</code>, <code>/product/*</code>',
			'<br /><code>' . __( 'WordPress Dashboard > Settings > Permalinks > Product permalinks Example: default: /product/accessories/action-figures/acme/ - becomes: /accessories/action-figures/acme/', 'rank-math' ) . '</code>'
		),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'wc_remove_category_base',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Remove category base', 'rank-math' ),
		'desc'    => esc_html__( 'Remove prefix from category URL.', 'rank-math' ) .
			'<br><code>' . esc_html__( 'default: /product-category/accessories/action-figures/ - changed: /accessories/action-figures/', 'rank-math' ) . '</code>',
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'wc_remove_category_parent_slugs',
		'type'    => 'toggle',
		'name'    => esc_html__( ' Remove parent slugs', 'rank-math' ),
		'desc'    => esc_html__( 'Remove parent slugs from category URL.', 'rank-math' ) .
			'<br><code>' . esc_html__( 'default: /product-category/accessories/action-figures/ - changed: /product-category/action-figures/', 'rank-math' ) . '</code>',
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'wc_remove_generator',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Remove Generator Tag', 'rank-math' ),
		'desc'    => esc_html__( 'Remove WooCommerce generator tag from the source code.', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'remove_shop_snippet_data',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Remove Schema Markup on Shop Archives', 'rank-math' ),
		'desc'    => esc_html__( 'Remove Schema Markup Data from WooCommerce Shop archive pages.', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'product_brand',
		'type'    => 'select',
		'name'    => esc_html__( 'Brand', 'rank-math' ),
		'desc'    => esc_html__( 'Select Product Brand Taxonomy to use in Schema.org & OpenGraph markup.', 'rank-math' ),
		'options' => Helper::get_object_taxonomies( 'product', 'choices', false ),
	]
);
