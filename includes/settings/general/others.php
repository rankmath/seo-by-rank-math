<?php
/**
 * The misc settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'frontend_seo_score',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Show SEO Score', 'rank-math' ),
		'desc'    => sprintf(
			/* translators: %s is the shortcode */
			esc_html__( 'Show the calculated SEO Score as a badge on the front end for selected post types. It can be disabled for specific posts.', 'rank-math' ),
			'<code>[rank_math_seo_score]</code>'
		),
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'         => 'frontend_seo_score_post_types',
		'type'       => 'multicheck',
		'name'       => esc_html__( 'SEO Score Post Types', 'rank-math' ),
		'options'    => Helper::choices_post_types(),
		'default_cb' => '\\RankMath\\Frontend_SEO_Score::post_types_field_default',
		'dep'        => [ [ 'frontend_seo_score', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'frontend_seo_score_template',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'SEO Score Template', 'rank-math' ),
		'desc'    => sprintf( esc_html__( 'Change the styling for the front end SEO score badge.', 'rank-math' ), '<code>nofollow</code>' ),
		'options' => [
			'circle' => esc_html__( 'Circle', 'rank-math' ),
			'square' => esc_html__( 'Square', 'rank-math' ),
		],
		'default' => 'circle',
		'dep'     => [ [ 'frontend_seo_score', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'frontend_seo_score_position',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'SEO Score Position', 'rank-math' ),
		'desc'    => sprintf(
			/* translators: 1.SEO Score Shortcode 2. SEO Score function */
			esc_html__( 'Display the badges automatically, or insert the %1$s shortcode in your posts and the %2$s template tag in your theme template files.', 'rank-math' ),
			'<code>[rank_math_seo_score]</code>',
			'<code>&lt;?php&nbsp;rank_math_the_seo_score();&nbsp;?&gt;</code>'
		),
		'classes' => 'nob',
		'default' => 'top',
		'options' => [
			'bottom' => esc_html__( 'Below Content', 'rank-math' ),
			'top'    => esc_html__( 'Above Content', 'rank-math' ),
			'both'   => esc_html__( 'Above & Below Content', 'rank-math' ),
			'custom' => esc_html__( 'Custom (use shortcode)', 'rank-math' ),
		],
		'dep'     => [ [ 'frontend_seo_score', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'support_rank_math',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Support Us with a Link', 'rank-math' ),
		/* Translators: %s is the word "nofollow" code tag and second one for the filter link */
		'desc'    => sprintf( esc_html__( 'If you are showing the SEO scores on the front end, this option will insert a %1$s backlink to RankMath.com to show your support. You can change the link & the text by using this %2$s.', 'rank-math' ), '<code>follow</code>', '<a href="https://rankmath.com/kb/filters-hooks-api-developer/#change-seo-score-backlink" target="_blank">' . __( 'filter', 'rank-math' ) . '</a>' ),
		'default' => 'on',
		'dep'     => [ [ 'frontend_seo_score', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'   => 'rss_before_content',
		'type' => 'textarea_small',
		'name' => esc_html__( 'RSS Before Content', 'rank-math' ),
		'desc' => esc_html__( 'Add content before each post in your site feeds.', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'rss_after_content',
		'type' => 'textarea_small',
		'name' => esc_html__( 'RSS After Content', 'rank-math' ),
		'desc' => esc_html__( 'Add content after each post in your site feeds.', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'   => 'rank_math_serp_preview',
		'type' => 'raw',
		'file' => rank_math()->includes_dir() . 'settings/general/rss-vars-table.php',
	]
);
