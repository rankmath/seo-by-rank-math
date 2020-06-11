<?php
/**
 * Sitemap - General
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

if ( class_exists( 'SitePress' ) ) {
	$cmb->add_field(
		[
			'id'      => 'multilingual_sitemap_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			'content' => sprintf(
				/* translators: hreflang tags documentation link */
				esc_html__( 'Rank Math generates the default Sitemaps only and WPML takes care of the rest. When a search engine bot visits any post/page, it is shown hreflang tag that helps it crawl the translated pages. This is one of the recommended methods by Google. Please %s', 'rank-math' ),
				'<a href="https://support.google.com/webmasters/answer/189077?hl=en" target="blank">' . esc_html__( 'read here', 'rank-math' ) . '</a>'
			),
		]
	);
}

$cmb->add_field(
	[
		'id'         => 'items_per_page',
		'type'       => 'text',
		'name'       => esc_html__( 'Links Per Sitemap', 'rank-math' ),
		'desc'       => esc_html__( 'Max number of links on each sitemap page.', 'rank-math' ),
		'default'    => '200',
		'attributes' => [ 'type' => 'number' ],
		'classes'    => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'include_images',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Images in Sitemaps', 'rank-math' ),
		'desc'    => esc_html__( 'Include reference to images from the post content in sitemaps. This helps search engines index the important images on your pages.', 'rank-math' ),
		'default' => 'on',
	]
);

$cmb->add_field(
	[
		'id'      => 'include_featured_image',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include Featured Images', 'rank-math' ),
		'desc'    => esc_html__( 'Include the Featured Image too, even if it does not appear directly in the post content.', 'rank-math' ),
		'default' => 'off',
		'dep'     => [ [ 'include_images', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'exclude_posts',
		'type'    => 'text',
		'name'    => esc_html__( 'Exclude Posts', 'rank-math' ),
		'desc'    => esc_html__( 'Enter post IDs of posts you want to exclude from the sitemap, separated by commas. This option **applies** to all posts types including posts, pages, and custom post types.', 'rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'exclude_terms',
		'type'    => 'text',
		'name'    => esc_html__( 'Exclude Terms', 'rank-math' ),
		'desc'    => esc_html__( 'Add term IDs, separated by comma. This option is applied for all taxonomies.', 'rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'ping_search_engines',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Ping Search Engines', 'rank-math' ),
		'desc'    => esc_html__( 'Automatically notify Google &amp; Bing when a sitemap gets updated.', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);
