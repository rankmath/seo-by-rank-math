<?php
/**
 * Sitemap - Authors
 *
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 */

use MyThemeShop\Helpers\WordPress;

$roles   = WordPress::get_roles();
$default = $roles;
unset( $default['administrator'], $default['editor'], $default['author'] );

$cmb->add_field( array(
	'id'                => 'exclude_roles',
	'type'              => 'multicheck',
	'name'              => esc_html__( 'Exclude User Roles', 'rank-math' ),
	'desc'              => esc_html__( 'Selected roles will be excluded in the sitemap.', 'rank-math' ),
	'options'           => $roles,
	'default'           => $default,
	'select_all_button' => false,
) );

$cmb->add_field( array(
	'id'   => 'exclude_users',
	'type' => 'text',
	'name' => esc_html__( 'Exclude Users', 'rank-math' ),
	'desc' => esc_html__( 'Add user IDs, separated by commas, to exclude them from the sitemap.', 'rank-math' ),
) );
