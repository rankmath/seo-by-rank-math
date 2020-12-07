<?php
/**
 * The authors settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$dep = [ [ 'disable_author_archives', 'off' ] ];

$cmb->add_field(
	[
		'id'      => 'disable_author_archives',
		'type'    => 'switch',
		'name'    => esc_html__( 'Author Archives', 'rank-math' ),
		'desc'    => esc_html__( 'Enables or disables Author Archives. If disabled, the Author Archives are redirected to your homepage. To avoid duplicate content issues, noindex author archives if you keep them enabled.', 'rank-math' ),
		'options' => [
			'on' => esc_html__( 'Disabled', 'rank-math' ),
			'off'  => esc_html__( 'Enabled', 'rank-math' ),
		],
		'default' => $this->do_filter( 'settings/titles/disable_author_archives', 'off' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'url_author_base',
		'type'    => 'text',
		'name'    => esc_html__( 'Author Base', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Change the <code>/author/</code> part in author archive URLs.', 'rank-math' ) ),
		'default' => 'author',
		'dep'     => $dep,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'author_custom_robots',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Author Robots Meta', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Select custom robots meta for author page, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.', 'rank-math' ) ),
		'options' => [
			'off' => esc_html__( 'Default', 'rank-math' ),
			'on'  => esc_html__( 'Custom', 'rank-math' ),
		],
		'default' => 'on',
		'dep'     => $dep,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'                => 'author_robots',
		'type'              => 'multicheck',
		/* translators: post type name */
		'name'              => esc_html__( 'Author Robots Meta', 'rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on author page.', 'rank-math' ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'classes'           => 'rank-math-advanced-option',
		'dep'               => [
			'relation' => 'and',
			[ 'author_custom_robots', 'on' ],
			[ 'disable_author_archives', 'off' ],
		],
	]
);

$cmb->add_field(
	[
		'id'              => 'author_advanced_robots',
		'type'            => 'advanced_robots',
		'name'            => esc_html__( 'Author Advanced Robots', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_advanced_robots' ],
		'classes'         => 'rank-math-advanced-option',
		'dep'             => [
			'relation' => 'and',
			[ 'author_custom_robots', 'on' ],
			[ 'disable_author_archives', 'off' ],
		],
	]
);

$cmb->add_field(
	[
		'id'              => 'author_archive_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Author Archive Title', 'rank-math' ),
		'desc'            => esc_html__( 'Title tag on author archives. SEO options for specific authors can be set with the meta box available in the user profiles.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		'default'         => '%name% %sep% %sitename% %page%',
		'dep'             => $dep,
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'data-exclude-variables' => 'seo_title,seo_description' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'author_archive_description',
		'type'       => 'textarea_small',
		'name'       => esc_html__( 'Author Archive Description', 'rank-math' ),
		'desc'       => esc_html__( 'Author archive meta description. SEO options for specific author archives can be set with the meta box in the user profiles.', 'rank-math' ),
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
		'id'      => 'author_add_meta_box',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add SEO Meta Box for Users', 'rank-math' ),
		'desc'    => esc_html__( 'Add SEO Meta Box for user profile pages. Access to the Meta Box can be fine tuned with code, using a special filter hook.', 'rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
		'dep'     => $dep,
	]
);
