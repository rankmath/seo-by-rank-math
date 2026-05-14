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
	$home_page_id = get_option( 'page_on_front' );
	if ( ! $home_page_id ) {
		$home_page_id = get_option( 'page_for_posts' );
	}

	$cmb->add_field(
		[
			'id'      => 'static_homepage_notice',
			'type'    => 'notice',
			'what'    => 'warning',
			'content' => sprintf(
				/* translators: something */
				esc_html__( 'Static page is set as the front page (WP Dashboard > Settings > Reading). To add SEO title, description, and meta for the homepage, please click here: %s', 'seo-by-rank-math' ),
				'<a href="' . admin_url( 'post.php?post=' . $home_page_id . '&action=edit' ) . '">' . esc_html__( 'Edit Page: ', 'seo-by-rank-math' ) . get_the_title( $home_page_id ) . '</a>'
			),
		]
	);
	return;
}

$cmb->add_field(
	[
		'id'              => 'homepage_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Homepage Title', 'seo-by-rank-math' ),
		'desc'            => esc_html__( 'Homepage title tag.', 'seo-by-rank-math' ),
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
		'name'       => esc_html__( 'Homepage Meta Description', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Homepage meta description.', 'seo-by-rank-math' ),
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
		'name'    => esc_html__( 'Homepage Robots Meta', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Select custom robots meta for homepage, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.', 'seo-by-rank-math' ) ),
		'options' => [
			'off' => esc_html__( 'Default', 'seo-by-rank-math' ),
			'on'  => esc_html__( 'Custom', 'seo-by-rank-math' ),
		],
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'homepage_robots',
		'type'              => 'multicheck',
		'name'              => esc_html__( 'Homepage Robots Meta', 'seo-by-rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on homepage.', 'seo-by-rank-math' ),
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
		'name'            => esc_html__( 'Homepage Advanced Robots', 'seo-by-rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => [ [ 'homepage_custom_robots', 'on' ] ],
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'homepage_facebook_title',
		'type'    => 'text',
		'name'    => esc_html__( 'Homepage Title for Facebook', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Title of your site when shared on Facebook, Twitter and other social networks.', 'seo-by-rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'homepage_facebook_description',
		'type'    => 'textarea_small',
		'name'    => esc_html__( 'Homepage Description for Facebook', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Description of your site when shared on Facebook, Twitter and other social networks.', 'seo-by-rank-math' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'   => 'homepage_facebook_image',
		'type' => 'file',
		'name' => esc_html__( 'Homepage Thumbnail for Facebook', 'seo-by-rank-math' ),
		'desc' => esc_html__( 'Image displayed when your homepage is shared on Facebook and other social networks. Use images that are at least 1200 x 630 pixels for the best display on high resolution devices.', 'seo-by-rank-math' ),
	]
);
