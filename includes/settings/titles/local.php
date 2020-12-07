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
		'id'      => 'knowledgegraph_name',
		'type'    => 'text',
		'name'    => esc_html__( 'Name', 'rank-math' ),
		'desc'    => esc_html__( 'Your name or company name', 'rank-math' ),
		'default' => get_bloginfo( 'name' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'knowledgegraph_logo',
		'type'    => 'file',
		'name'    => esc_html__( 'Logo', 'rank-math' ),
		'desc'    => __( '<strong>Min Size: 160Χ90px, Max Size: 1920X1080px</strong>.<br /> A squared image is preferred by the search engines.', 'rank-math' ),
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
