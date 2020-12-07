<?php
/**
 * The BuddyPress groups settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'              => 'bp_group_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Group Title', 'rank-math' ),
		'desc'            => esc_html__( 'Title tag for groups', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'bp_group_description',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Group Description', 'rank-math' ),
		'desc'       => esc_html__( 'BuddyPress group description', 'rank-math' ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'attributes' => [
			'data-exclude-variables' => 'seo_title,seo_description',
			'rows'                   => 2,
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'bp_group_custom_robots',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Group Robots Meta', 'rank-math' ),
		'desc'    => __( 'Select custom robots meta for Group archive pages. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ),
		'options' => [
			'off' => esc_html__( 'Default', 'rank-math' ),
			'on'  => esc_html__( 'Custom', 'rank-math' ),
		],
		'default' => $custom_default,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'bp_group_robots',
		'type'              => 'multicheck',
		'name'              => esc_html__( 'Group Robots Meta', 'rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on groups page.', 'rank-math' ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => [ [ 'bp_group_custom_robots', 'on' ] ],
		'classes'           => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'              => 'bp_group_advanced_robots',
		'type'            => 'advanced_robots',
		'name'            => esc_html__( 'Group Advanced Robots Meta', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'bp_group_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);
