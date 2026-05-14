<?php
/**
 * Sitemap settings - HTML Sitemap tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'html_sitemap',
		'type'    => 'toggle',
		'name'    => esc_html__( 'HTML Sitemap', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Enable the HTML sitemap.', 'seo-by-rank-math' ),
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'      => 'html_sitemap_display',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Display Format', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Choose how you want to display the HTML sitemap.', 'seo-by-rank-math' ),
		'options' => [
			'shortcode' => esc_html__( 'Shortcode', 'seo-by-rank-math' ),
			'page'      => esc_html__( 'Page', 'seo-by-rank-math' ),
		],
		'default' => 'shortcode',
		'dep'     => [ [ 'html_sitemap', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'         => 'html_sitemap_shortcode',
		'type'       => 'text',
		'name'       => esc_html__( 'Shortcode', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Use this shortcode to display the HTML sitemap.', 'seo-by-rank-math' ),
		'default'    => '[rank_math_html_sitemap]',
		'dep'        => [
			'relation' => 'AND',
			[ 'html_sitemap', 'on' ],
			[ 'html_sitemap_display', 'shortcode' ],
		],
		'classes'    => 'rank-math-code',
		'attributes' => [
			'disabled' => 'disabled',
		],
	]
);

$rank_math_sitemap_page         = Helper::get_settings( 'sitemap.html_sitemap_page' );
$rank_math_sitemap_page_options = [ '' => __( 'Select Page', 'seo-by-rank-math' ) ];
$rank_math_sitemap_page_after   = '<p class="rank-math-selected-page-message hidden">' . esc_html__( 'Selected page: ', 'seo-by-rank-math' ) . '<span class="rank-math-selected-page"></span></p>';

if ( $rank_math_sitemap_page ) {
	$rank_math_sitemap_page_options[ $rank_math_sitemap_page ] = get_the_title( $rank_math_sitemap_page );

	$rank_math_sitemap_page_after = '<p class="rank-math-selected-page-message">' . sprintf(
		/* translators: link to the selected page */
		__( 'Selected page: <a href="%s" target="_blank" class="rank-math-selected-page">%s</a>', 'seo-by-rank-math' ), // phpcs:ignore
		get_permalink( $rank_math_sitemap_page ),
		get_permalink( $rank_math_sitemap_page )
	) . '</p>';
}

$cmb->add_field(
	[
		'id'         => 'html_sitemap_page',
		'type'       => 'select',
		'name'       => esc_html__( 'Page', 'seo-by-rank-math' ),
		'desc'       => esc_html__( 'Select the page to display the HTML sitemap. Once the settings are saved, the sitemap will be displayed below the content of the selected page.', 'seo-by-rank-math' ),
		'options'    => $rank_math_sitemap_page_options,
		'dep'        => [
			'relation' => 'AND',
			[ 'html_sitemap', 'on' ],
			[ 'html_sitemap_display', 'page' ],
		],
		'after'      => $rank_math_sitemap_page_after,
		'attributes' => [
			'data-placeholder' => esc_html__( 'Select a page', 'seo-by-rank-math' ),
			'data-s2-pages'    => 'true',
		],
	]
);

$cmb->add_field(
	[
		'id'      => 'html_sitemap_sort',
		'type'    => 'select',
		'name'    => esc_html__( 'Sort By', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Choose how you want to sort the items in the HTML sitemap.', 'seo-by-rank-math' ),
		'options' => [
			// Published Date, Modified Date, Alphabetical, Post ID.
			'published'    => esc_html__( 'Published Date', 'seo-by-rank-math' ),
			'modified'     => esc_html__( 'Modified Date', 'seo-by-rank-math' ),
			'alphabetical' => esc_html__( 'Alphabetical', 'seo-by-rank-math' ),
			'post_id'      => esc_html__( 'Post ID', 'seo-by-rank-math' ),
		],
		'default' => 'published',
		'dep'     => [ [ 'html_sitemap', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'html_sitemap_show_dates',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Show Dates', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Show published dates for each post & page.', 'seo-by-rank-math' ),
		'default' => 'on',
		'dep'     => [ [ 'html_sitemap', 'on' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'html_sitemap_seo_titles',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Item Titles', 'seo-by-rank-math' ),
		'desc'    => esc_html__( 'Show the post/term titles, or the SEO titles in the HTML sitemap.', 'seo-by-rank-math' ),
		'options' => [
			'titles'     => esc_html__( 'Item Titles', 'seo-by-rank-math' ),
			'seo_titles' => esc_html__( 'SEO Titles', 'seo-by-rank-math' ),
		],
		'dep'     => [ [ 'html_sitemap', 'on' ] ],
		'default' => 'titles',
	]
);
