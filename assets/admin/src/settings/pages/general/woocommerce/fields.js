/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

export default [
	{
		id: 'wc_remove_product_base',
		type: 'toggle',
		name: __( 'Remove base', 'rank-math' ),
		desc: sprintf(
			/* translators: 1. Example text 2. Example text */
			__(
				'Remove prefix like %1$s from product URL chosen at %2$s',
				'rank-math'
			),
			'<code>/shop/*</code>, <code>/product/*</code>',
			'<br /><code>' + __( 'WordPress Dashboard > Settings > Permalinks > Product permalinks Example: default: /product/accessories/action-figures/acme/ - becomes: /accessories/action-figures/acme/', 'rank-math' ) + '</code>'
		),
		default: false,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'wc_remove_category_base',
		type: 'toggle',
		name: __( 'Remove category base', 'rank-math' ),
		desc: __( 'Remove prefix from category URL.', 'rank-math' ) +
			'<br><code>' + __( 'default: /product-category/accessories/action-figures/ - changed: /accessories/action-figures/', 'rank-math' ) + '</code>',
		default: false,
		classes: 'rank-math-advanced-option',
	},

	{
		id: 'wc_remove_category_parent_slugs',
		type: 'toggle',
		name: __( ' Remove parent slugs', 'rank-math' ),
		desc: __( 'Remove parent slugs from category URL.', 'rank-math' ) +
			'<br><code>' + __( 'default: /product-category/accessories/action-figures/ - changed: /product-category/action-figures/', 'rank-math' ) + '</code>',
		default: false,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'wc_remove_generator',
		type: 'toggle',
		name: __( 'Remove Generator Tag', 'rank-math' ),
		desc: __( 'Remove WooCommerce generator tag from the source code.', 'rank-math' ),
		default: true,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'remove_shop_snippet_data',
		type: 'toggle',
		name: __( 'Remove Schema Markup on Shop Archives', 'rank-math' ),
		desc: __( 'Remove Schema Markup Data from WooCommerce Shop archive pages.', 'rank-math' ),
		default: true,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'product_brand',
		type: 'select',
		name: __( 'Select Brand', 'rank-math' ),
		desc: __( 'Select Product Brand Taxonomy to use in Schema.org & OpenGraph markup.', 'rank-math' ),
		options: rankMath.brandTaxonomies,
	},
]
