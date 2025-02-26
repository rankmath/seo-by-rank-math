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
		'name'    => esc_html__( 'Person or Company', 'rank-math' ),
		'options' => [
			'person'  => esc_html__( 'Person', 'rank-math' ),
			'company' => esc_html__( 'Organization', 'rank-math' ),
		],
		'desc'    => esc_html__( 'Choose whether the site represents a person or an organization.', 'rank-math' ),
		'default' => 'person',
	]
);

$cmb->add_field(
	[
		'id'      => 'website_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Website Name', 'rank-math' ),
		'desc'    => esc_html__( 'Enter the name of your site to appear in search results.', 'rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'website_alternate_name',
		'type' => 'text',
		'name' => esc_html__( 'Website Alternate Name', 'rank-math' ),
		'desc' => esc_html__( 'An alternate version of your site name (for example, an acronym or shorter name).', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Person/Organization Name', 'rank-math' ),
		'desc'    => esc_html__( 'Your name or company name intended to feature in Google\'s Knowledge Panel.', 'rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_logo',
		'type'    => 'file',
		'name'    => esc_html__( 'Logo', 'rank-math' ),
		'desc'    => __( '<strong>Min Size: 112Χ112px</strong>.<br /> A squared image is preferred by the search engines.', 'rank-math' ),
		'options' => [ 'url' => false ],
	]
);

$cmb->add_field(
	[
		'id'      => 'url',
		'type'    => 'text',
		'name'    => esc_html__( 'URL', 'rank-math' ),
		'desc'    => esc_html__( 'URL of the item.', 'rank-math' ),
		'default' => home_url(),
	]
);
