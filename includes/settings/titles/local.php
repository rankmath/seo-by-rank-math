<?php
/**
 * The local SEO settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Local_Seo
 */

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Person or Company', 'seo-by-rank-math' ),
		'options' => [
			'person'  => esc_html__( 'Person', 'seo-by-rank-math' ),
			'company' => esc_html__( 'Organization', 'seo-by-rank-math' ),
		],
		'desc'    => esc_html__( 'Choose whether the site represents a person or an organization.', 'seo-by-rank-math' ),
		'default' => 'person',
	]
);

$cmb->add_field(
	[
		'id'      => 'website_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Website Name', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enter the name of your site to appear in search results.', 'seo-by-rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'website_alternate_name',
		'type' => 'text',
		'name' => esc_html__( 'Website Alternate Name', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'An alternate version of your site name (for example, an acronym or shorter name).', 'seo-by-rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Person/Organization Name', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Your name or company name intended to feature in Google\'s Knowledge Panel.', 'seo-by-rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_logo',
		'type'    => 'file',
		'name'    => esc_html__( 'Logo', 'seo-by-rank-math' ),
		'desc'    => __( '<strong>Min Size: 112Χ112px</strong>.<br /> A squared image is preferred by the search engines.', 'seo-by-rank-math' ),
		'options' => [ 'url' => false ],
	]
);

$cmb->add_field(
	[
		'id'      => 'url',
		'type'    => 'text',
		'name'    => esc_html__( 'URL', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'URL of the item.', 'seo-by-rank-math' ),
		'default' => home_url(),
	]
);
