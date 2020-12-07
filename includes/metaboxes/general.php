<?php
/**
 * Metabox - General Tab
 *
 * @package    RankMath
 * @subpackage RankMath\Metaboxes
 */

use RankMath\KB;
use RankMath\Helper;
use MyThemeShop\Helpers\WordPress;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'   => 'rank_math_serp_preview',
		'type' => 'raw',
		'file' => rank_math()->includes_dir() . 'metaboxes/serp-preview.php',
	]
);

$serp_footer = '<div class="serp-preview-footer wp-clearfix">
			<div class="rank-math-ui">
				<a href="#" class="button button-primary rank-math-edit-snippet">' . esc_html__( 'Edit Snippet', 'rank-math' ) . '</a>
				<a href="#" class="button button-secondary rank-math-edit-snippet hidden">' . esc_html__( 'Close Editor', 'rank-math' ) . '</a>
			</div>
		</div>';

$cmb->add_field(
	[
		'id'              => 'rank_math_title',
		'type'            => 'text',
		'name'            => esc_html__( 'Title', 'rank-math' ),
		'desc'            => esc_html__( 'This is what will appear in the first line when this post shows up in the search results.', 'rank-math' ),
		'classes'         => 'rank-math-supports-variables',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [
			'class'                  => 'regular-text wp-exclude-emoji',
			'data-gramm_editor'      => 'false',
			'data-exclude-variables' => 'seo_title,seo_description',
		],
		'before_row'      => '<div class="rank-math-serp-fields-wrapper hidden">',
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_permalink',
		'type'            => 'text',
		'name'            => esc_html__( 'Permalink', 'rank-math' ),
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_permalink' ],
		'escape_cb'       => [ '\RankMath\CMB2', 'escape_permalink' ],
		'desc'            => Admin_Helper::is_home_page() ? esc_html__( 'Editing Homepage permalink is not possible.', 'rank-math' ) : esc_html__( 'This is the unique URL of this page, displayed below the post title in the search results.', 'rank-math' ),
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_description',
		'type'       => 'textarea',
		'name'       => esc_html__( 'Description', 'rank-math' ),
		'desc'       => esc_html__( 'This is what will appear as the description when this post shows up in the search results.', 'rank-math' ),
		'classes'    => 'rank-math-supports-variables',
		'escape_cb'  => 'esc_html',
		'attributes' => [
			'class'                  => 'cmb2_textarea wp-exclude-emoji',
			'rows'                   => 2,
			'data-autoresize'        => true,
			'data-gramm_editor'      => 'false',
			'data-exclude-variables' => 'seo_title,seo_description',
		],
		'after_row'  => '</div>' . $serp_footer,
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_focus_keyword',
		'type'            => 'text',
		'name'            => esc_html__( 'Focus Keyword', 'rank-math' ),
		/* translators: Link to kb article */
		'desc'            => sprintf( wp_kses_post( __( 'Insert keywords you want to rank for. Try to <a href="%s" target="_blank">attain 100/100 points</a> for better chances of ranking.', 'rank-math' ) ), \RankMath\KB::get( 'score-100-ce' ) ),
		'after_field'     => apply_filters(
			'rank_math/analytics/classic/pro_notice',
			'<div class="notice notice-warning inline rank-math-notice"><p>' . sprintf(
				/* translators: link to pricing page. */
				__( 'Want more? %s version', 'rank-math' ),
				'<a href="' . KB::get( 'pro-general-ce' ) . '" target="_blank"><strong>' . __( 'Upgrade today to the PRO', 'rank-math' ) . '</strong></a>'
			) . '</p></div>'
		),
		'before'          => '<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=CE%20General%20Tab%20Trends&utm_campaign=WP" id="rank-math-compare-keywords-trigger" class="rank-math-compare-keywords-trigger button button-secondary" target="_blank" title="' . esc_attr__( 'Trends', 'rank-math' ) . '">' . Admin_Helper::get_trends_icon_svg() . '</a>',
		'classes'         => 'nob',
		'attributes'      => [
			'placeholder' => esc_html__( 'Example: Rank Math SEO', 'rank-math' ),
		],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_focus_keywords' ],
	]
);

if ( ! Admin_Helper::is_term_profile_page() ) {
	$cmb->add_field(
		[
			'id'      => 'rank_math_pillar_content',
			'type'    => 'checkbox',
			'name'    => '&nbsp;',
			'classes' => 'nob nopt',
			'desc'    => '<strong>' . esc_html__( 'This post is Pillar Content', 'rank-math' ) . '</strong>' .
				Admin_Helper::get_tooltip( esc_html__( 'Select one or more Pillar Content posts for each post tag or category to show them in the Link Suggestions meta box.', 'rank-math' ) ),
		]
	);
}

/**
 * Allow disabling the primary term feature.
 *
 * @param bool $return True to disable.
 */
if ( false === apply_filters_deprecated( 'rank_math/primary_term', [ false ], '1.0.43', 'rank_math/admin/disable_primary_term' )
	&& false === $this->do_filter( 'admin/disable_primary_term', false ) ) {
	$taxonomies = Helper::get_object_taxonomies( WordPress::get_post_type(), 'objects' );
	$taxonomies = wp_filter_object_list( $taxonomies, [ 'hierarchical' => true ], 'and', 'name' );
	foreach ( $taxonomies as $taxonomy ) {
		$cmb->add_field(
			[
				'id'         => 'rank_math_primary_' . $taxonomy,
				'type'       => 'hidden',
				'default'    => 0,
				'attributes' => [
					'data-primary-term' => $taxonomy,
				],
			]
		);
	}
}

// SEO Score.
$cmb->add_field(
	[
		'id'   => 'rank_math_seo_score',
		'type' => 'hidden',
	]
);
