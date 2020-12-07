<?php
/**
 * Sitemap - Post Types
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use RankMath\Helper;

$attributes = [];
$post_type  = $tab['post_type'];
$prefix     = "pt_{$post_type}_";

if ( 'attachment' === $post_type && Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
	$cmb->add_field(
		[
			'id'      => 'attachment_redirect_urls_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			'content' => esc_html__( 'To generate attachment sitemap disable attachment redirection to parent.', 'rank-math' ),
		]
	);
	$attributes['disabled'] = 'disabled';
}

$cmb->add_field(
	[
		'id'         => $prefix . 'sitemap',
		'type'       => 'toggle',
		'name'       => esc_html__( 'Include in Sitemap', 'rank-math' ),
		'desc'       => esc_html__( 'Include this post type in the XML sitemap.', 'rank-math' ),
		'default'    => 'attachment' === $post_type ? 'off' : 'on',
		'attributes' => $attributes,
	]
);

if ( 'attachment' !== $post_type ) {
	$cmb->add_field(
		[
			'id'      => $prefix . 'image_customfields',
			'type'    => 'textarea_small',
			'name'    => esc_html__( 'Image Custom Fields', 'rank-math' ),
			'desc'    => esc_html__( 'Insert custom field (post meta) names which contain image URLs to include them in the sitemaps. Add one per line.', 'rank-math' ),
			'dep'     => [ [ $prefix . 'sitemap', 'on' ] ],
			'classes' => 'rank-math-advanced-option',
		]
	);
}
