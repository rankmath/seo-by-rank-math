<?php
/**
 * The misc settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

$dep = [ [ 'disable_date_archives', 'off' ] ];

$cmb->add_field(
	[
		'id'      => 'disable_date_archives',
		'type'    => 'switch',
		'name'    => esc_html__( 'Date Archives', 'rank-math' ),
		'desc'    => esc_html__( 'Enable or disable the date archive (_e.g: domain.com/2019/06/_). If this option is disabled, the date archives will be redirected to the homepage.', 'rank-math' ),
		'options' => [
			'off' => esc_html__( 'Enabled', 'rank-math' ),
			'on'  => esc_html__( 'Disabled', 'rank-math' ),
		],
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'              => 'date_archive_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Date Archive Title', 'rank-math' ),
		'desc'            => esc_html__( 'Title tag on day/month/year based archives.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		'default'         => '%date% %page% %sep% %sitename%',
		'dep'             => $dep,
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'date_archive_description',
		'type'       => 'textarea_small',
		'name'       => esc_html__( 'Date Archive Description', 'rank-math' ),
		'desc'       => esc_html__( 'Date archive description.', 'rank-math' ),
		'classes'    => 'rank-math-supports-variables rank-math-description rank-math-advanced-option',
		'dep'        => $dep,
		'attributes' => [
			'class'                  => 'cmb2-textarea-small wp-exclude-emoji',
			'data-gramm_editor'      => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
	]
);

$cmb->add_field(
	[
		'id'                => 'date_archive_robots',
		'type'              => 'multicheck',
		/* translators: post type name */
		'name'              => esc_html__( 'Date Robots Meta', 'rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on date page.', 'rank-math' ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'dep'               => $dep,
		'classes'           => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'              => 'date_advanced_robots',
		'type'            => 'advanced_robots',
		'name'            => esc_html__( 'Date Advanced Robots', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'dep'             => $dep,
		'classes'         => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'              => '404_title',
		'type'            => 'text',
		'name'            => esc_html__( '404 Title', 'rank-math' ),
		'desc'            => esc_html__( 'Title tag on 404 Not Found error page.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		'default'         => 'Page Not Found %sep% %sitename%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'search_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Search Results Title', 'rank-math' ),
		'desc'            => esc_html__( 'Title tag on search results page.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		'default'         => '%search_query% %page% %sep% %sitename%',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'      => 'noindex_search',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Noindex Search Results', 'rank-math' ),
		'desc'    => esc_html__( 'Prevent search results pages from getting indexed by search engines. Search results could be considered to be thin content and prone to duplicate content issues.', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'noindex_paginated_pages',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Noindex Paginated Pages', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Set this to on to prevent /page/2 and further of any archive to show up in the search results.', 'rank-math' ) ),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'noindex_archive_subpages',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Noindex Archive Subpages', 'rank-math' ),
		'desc'    => esc_html__( 'Prevent paginated archive pages from getting indexed by search engines.', 'rank-math' ),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'noindex_password_protected',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Noindex Password Protected Pages', 'rank-math' ),
		'desc'    => esc_html__( 'Prevent password protected pages & posts from getting indexed by search engines.', 'rank-math' ),
		'default' => 'off',
		'classes' => 'rank-math-advanced-option',
	]
);
