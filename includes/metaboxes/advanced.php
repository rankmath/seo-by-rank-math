<?php
/**
 * Metabox - Advance Tab
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

$robot_index = [
	'index' => esc_html__( 'Index', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Instructs search engines to index and show these pages in the search results.', 'rank-math' ) ),
];

$cmb->add_field(
	[
		'id'                => 'rank_math_robots',
		'type'              => 'multicheck',
		'name'              => esc_html__( 'Robots Meta', 'rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag.', 'rank-math' ),
		'options'           => Helper::choices_robots(),
		'default_cb'        => '\\RankMath\\Helper::get_robots_defaults',
		'select_all_button' => false,
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_advanced_robots',
		'type'            => 'advanced_robots',
		'name'            => esc_html__( 'Advanced Robots Meta', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
	]
);

$cmb->add_field(
	[
		'id'   => 'rank_math_canonical_url',
		'type' => 'text_url',
		'name' => esc_html__( 'Canonical URL', 'rank-math' ),
		'desc' => esc_html__( 'The canonical URL informs search crawlers which page is the main page if you have double content.', 'rank-math' ),
	]
);

if ( Helper::get_settings( 'general.breadcrumbs' ) ) {
	$cmb->add_field(
		[
			'id'   => 'rank_math_breadcrumb_title',
			'type' => 'text',
			'name' => esc_html__( 'Breadcrumb Title', 'rank-math' ),
			'desc' => esc_html__( 'Breadcrumb Title to use for this post', 'rank-math' ),
		]
	);
}
