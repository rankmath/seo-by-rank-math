<?php
/**
 * The breadcrumb settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$args = [
	'id'      => 'breadcrumbs',
	'type'    => 'toggle',
	'name'    => esc_html__( 'Enable breadcrumbs function', 'rank-math' ),
	'desc'    => esc_html__( 'Turning off breadcrumbs will hide breadcrumbs inserted in template files too.', 'rank-math' ),
	'default' => 'on',
];

if ( current_theme_supports( 'rank-math-breadcrumbs' ) ) {
	$args['force_enable'] = true;
	$args['disabled']     = true;
	$args['desc']         = sprintf(
		// Translators: Code to add support for Rank Math Breadcrumbs.
		esc_html__( 'This option cannot be changed since your theme has added the support for Rank Math Breadcrumbs using: %s', 'rank-math' ),
		"<br /><code>add_theme_support( 'rank-math-breadcrumbs' );</code>"
	);
}
$cmb->add_field( $args );

$dependency = [ [ 'breadcrumbs', 'on' ] ];
$cmb->add_field(
	[
		'id'              => 'breadcrumbs_separator',
		'type'            => 'radio_inline',
		'name'            => esc_html__( 'Separator Character', 'rank-math' ),
		'desc'            => esc_html__( 'Separator character or string that appears between breadcrumb items.', 'rank-math' ),
		'options'         => Helper::choices_separator( Helper::get_settings( 'general.breadcrumbs_separator' ) ),
		'default'         => '-',
		'dep'             => $dependency,
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_htmlentities' ],
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_home',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Show Homepage Link', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Display homepage breadcrumb in trail.', 'rank-math' ) ),
		'default' => 'on',
		'dep'     => $dependency,
	]
);

$dependency_home   = [ 'relation' => 'and' ] + $dependency;
$dependency_home[] = [ 'breadcrumbs_home', 'on' ];
$cmb->add_field(
	[
		'id'      => 'breadcrumbs_home_label',
		'type'    => 'text',
		'name'    => esc_html__( 'Homepage label', 'rank-math' ),
		'desc'    => esc_html__( 'Label used for homepage link (first item) in breadcrumbs.', 'rank-math' ),
		'default' => esc_html__( 'Home', 'rank-math' ),
		'dep'     => $dependency_home,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_home_link',
		'type'    => 'text',
		'name'    => esc_html__( 'Homepage Link', 'rank-math' ),
		'desc'    => esc_html__( 'Link to use for homepage (first item) in breadcrumbs.', 'rank-math' ),
		'default' => get_home_url(),
		'dep'     => $dependency_home,
	]
);

$cmb->add_field(
	[
		'id'   => 'breadcrumbs_prefix',
		'type' => 'text',
		'name' => esc_html__( 'Prefix Breadcrumb', 'rank-math' ),
		'desc' => esc_html__( 'Prefix for the breadcrumb path.', 'rank-math' ),
		'dep'  => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_archive_format',
		'type'    => 'text',
		'name'    => esc_html__( 'Archive Format', 'rank-math' ),
		'desc'    => esc_html__( 'Format the label used for archive pages.', 'rank-math' ),
		/* translators: placeholder */
		'default' => esc_html__( 'Archives for %s', 'rank-math' ),
		'dep'     => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_search_format',
		'type'    => 'text',
		'name'    => esc_html__( 'Search Results Format', 'rank-math' ),
		'desc'    => esc_html__( 'Format the label used for search results pages.', 'rank-math' ),
		/* translators: placeholder */
		'default' => esc_html__( 'Results for %s', 'rank-math' ),
		'dep'     => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_404_label',
		'type'    => 'text',
		'name'    => esc_html__( '404 label', 'rank-math' ),
		'desc'    => esc_html__( 'Label used for 404 error item in breadcrumbs.', 'rank-math' ),
		'default' => esc_html__( '404 Error: page not found', 'rank-math' ),
		'dep'     => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_remove_post_title',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Hide Post Title', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Hide Post title from Breadcrumb.', 'rank-math' ) ),
		'default' => 'off',
		'dep'     => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_ancestor_categories',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Show Category(s)', 'rank-math' ),
		'desc'    => esc_html__( 'If category is a child category, show all ancestor categories.', 'rank-math' ),
		'default' => 'off',
		'dep'     => $dependency,
	]
);

$cmb->add_field(
	[
		'id'      => 'breadcrumbs_hide_taxonomy_name',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Hide Taxonomy Name', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'Hide Taxonomy Name from Breadcrumb.', 'rank-math' ) ),
		'default' => 'off',
		'dep'     => $dependency,
	]
);

if ( 'page' === get_option( 'show_on_front' ) && 0 < get_option( 'page_for_posts' ) ) {
	$cmb->add_field(
		[
			'id'      => 'breadcrumbs_blog_page',
			'type'    => 'toggle',
			'name'    => esc_html__( 'Show Blog Page', 'rank-math' ),
			'desc'    => esc_html__( 'Show Blog Page in Breadcrumb.', 'rank-math' ),
			'default' => 'off',
			'dep'     => $dependency,
		]
	);
}
