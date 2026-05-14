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
		'name'    => esc_html__( 'Author Archives', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enables or disables Author Archives. If disabled, the Author Archives are redirected to your homepage. To avoid duplicate content issues, noindex author archives if you keep them enabled.', 'seo-by-rank-math' ),
		'options' => [
			'on'  => esc_html__( 'Disabled', 'seo-by-rank-math' ),
			'off' => esc_html__( 'Enabled', 'seo-by-rank-math' ),
		],
		'default' => $this->do_filter( 'settings/titles/disable_author_archives', 'off' ),
	]
);

$cmb->add_field(
	[
		'id'      => 'url_author_base',
		'type'    => 'text',
		'name'    => esc_html__( 'Author Base', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Change the <code>/author/</code> part in author archive URLs.', 'seo-by-rank-math' ) ),
		'default' => 'author',
		'dep'     => $dep,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'author_custom_robots',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Author Robots Meta', 'seo-by-rank-math' ),
		'desc'    => wp_kses_post( __( 'Select custom robots meta for author page, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.', 'seo-by-rank-math' ) ),
		'options' => [
			'off' => esc_html__( 'Default', 'seo-by-rank-math' ),
			'on'  => esc_html__( 'Custom', 'seo-by-rank-math' ),
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
		'name'              => esc_html__( 'Author Robots Meta', 'seo-by-rank-math' ),
		'desc'              => esc_html__( 'Custom values for robots meta tag on author page.', 'seo-by-rank-math' ),
		'options'           => Helper::choices_robots(),
		'select_all_button' => false,
		'classes'           => 'rank-math-advanced-option rank-math-robots-data',
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
		'name'            => esc_html__( 'Author Advanced Robots', 'seo-by-rank-math' ),
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
		'name'            => esc_html__( 'Author Archive Title', 'seo-by-rank-math' ),
		'desc'            => esc_html__( 'Title tag on author archives. SEO options for specific authors can be set with the meta box available in the user profiles.', 'seo-by-rank-math' ),
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
		'name'       => esc_html__( 'Author Archive Description', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Author archive meta description. SEO options for specific author archives can be set with the meta box in the user profiles.', 'seo-by-rank-math' ),
		'classes'    => 'rank-math-supports-variables rank-math-description rank-math-advanced-option',
		'dep'        => $dep,
		'attributes' => [
			'class'                  => 'cmb2-textarea-small wp-exclude-emoji',
			'data-gramm'             => 'false',
			'rows'                   => 2,
			'data-exclude-variables' => 'seo_title,seo_description',
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'author_slack_enhanced_sharing',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Slack Enhanced Sharing', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'When the option is enabled and an author archive is shared on Slack, additional information will be shown (name & total number of posts).', 'seo-by-rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
		'dep'     => $dep,
	]
);

$cmb->add_field(
	[
		'id'      => 'author_add_meta_box',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Add SEO Controls', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Add SEO Controls for user profile pages. Access to the Meta Box can be fine tuned with code, using a special filter hook.', 'seo-by-rank-math' ),
		'default' => 'on',
		'classes' => 'rank-math-advanced-option',
		'dep'     => $dep,
	]
);
