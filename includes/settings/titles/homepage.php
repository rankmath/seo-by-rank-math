<?php
/**
 * The homepage/frontpage settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

if ( 'page' === get_option( 'show_on_front' ) ) {
	$cmb->add_field(
		[
			'id'      => 'static_homepage_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			'content' => sprintf(
				/* translators: something */
				esc_html__( 'Static page is set as the front page (WP Dashboard > Settings > Reading). To add SEO title, description, and meta for the homepage, please click here: %s', 'rank-math' ),
				'<a href="' . admin_url( 'post.php?post=' . get_option( 'page_on_front' ) ) . '&action=edit' . '">' . esc_html__( 'Edit Page: ', 'rank-math' ) . get_the_title( get_option( 'page_on_front' ) ) . '</a>'
			),
		]
	);
	return;
}

$cmb->add_field(
	[
		'id'              => 'homepage_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Homepage Title', 'rank-math' ),
		'desc'            => esc_html__( 'Homepage title tag.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title',
		'default'         => '%sitename% %page% %sep% %sitedesc%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'homepage_description',
		'type'       => 'textarea_small',
		'name'       => esc_html__( 'Homepage Meta Description', 'rank-math' ),
		'desc'       => esc_html__( 'Homepage meta description.', 'rank-math' ),
		'classes'    => 'rank-math-supports-variables rank-math-description',
		'attributes' => [
			'class'                  => 'cmb2_textarea wp-exclude-emoji',
			'data-gramm'             => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'homepage_custom_robots',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Homepage Robots Meta', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Select custom robots meta for homepage, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ) ),
		'options' => [
			'off' => esc_html__( 'Default', 'rank-math' ),
			'on'  => esc_html__( 'Custom', 'rank-math' ),
		],
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'homepage_robots',
		'type'              => 'multicheck',
		'name'              => esc_html__( 'Homepage Robots Meta', 'rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on homepage.', 'rank-math' ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => [ [ 'homepage_custom_robots', 'on' ] ],
		'classes'           => 'rank-math-advanced-option rank-math-robots-data',
		'default'           => [ 'index' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'homepage_advanced_robots',
		'type'            => 'advanced_robots',
		'name'            => esc_html__( 'Homepage Advanced Robots', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'homepage_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'homepage_facebook_title',
		'type'    => 'text',
		'name'    => esc_html__( 'Homepage Title for Facebook', 'rank-math' ),
		'desc'    => esc_html__( 'Title of your site when shared on Facebook, Twitter and other social networks.', 'rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'homepage_facebook_description',
		'type'    => 'textarea_small',
		'name'    => esc_html__( 'Homepage Description for Facebook', 'rank-math' ),
		'desc'    => esc_html__( 'Description of your site when shared on Facebook, Twitter and other social networks.', 'rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'   => 'homepage_facebook_image',
		'type' => 'file',
		'name' => esc_html__( 'Homepage Thumbnail for Facebook', 'rank-math' ),
		'desc' => esc_html__( 'Image displayed when your homepage is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.', 'rank-math' ),
	]
);
