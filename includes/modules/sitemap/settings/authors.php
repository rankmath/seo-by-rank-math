<?php
/**
 * Sitemap settings - Authors tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$roles   = Helper::get_roles();
$default = $roles;
unset( $default['administrator'], $default['editor'], $default['author'] );

$dep = [
	'relation' => 'OR',
	[ 'authors_sitemap', 'on' ],
	[ 'authors_html_sitemap', 'on' ],
];

$cmb->add_field(
	[
		'id'      => 'authors_sitemap',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include in Sitemap', 'rank-math' ),
		'desc'    => esc_html__( 'Include author archives in the XML sitemap.', 'rank-math' ),
		'default' => 'on',
	]
);

$cmb->add_field(
	[
		'id'      => 'authors_html_sitemap',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Include in HTML Sitemap', 'rank-math' ),
		'desc'    => esc_html__( 'Include author archives in the HTML sitemap if it\'s enabled.', 'rank-math' ),
		'default' => 'on',
		'classes' => [
			'rank-math-html-sitemap',
			! Helper::get_settings( 'sitemap.html_sitemap' ) ? 'hidden' : '',
		],
	]
);

$cmb->add_field(
	[
		'id'                => 'exclude_roles',
		'type'              => 'multicheck',
		'name'              => esc_html__( 'Exclude User Roles', 'rank-math' ),
		'desc'              => esc_html__( 'Selected roles will be excluded from the XML &amp; HTML sitemaps.', 'rank-math' ),
		'options'           => $roles,
		'default'           => $default,
		'select_all_button' => false,
		'dep'               => $dep,
	]
);

$cmb->add_field(
	[
		'id'   => 'exclude_users',
		'type' => 'text',
		'name' => esc_html__( 'Exclude Users', 'rank-math' ),
		'desc' => esc_html__( 'Add user IDs, separated by commas, to exclude them from the sitemap.', 'rank-math' ),
		'dep'  => $dep,
	]
);
