<?php
/**
 * Redirections general settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'redirections_debug',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Debug Redirections', 'rank-math' ),
		'desc'    => esc_html__( 'Display the Debug Console instead of being redirected. Administrators only.', 'rank-math' ),
		'default' => 'off',
	]
);

$cmb->add_field(
	[
		'id'      => 'redirections_fallback',
		'type'    => 'radio',
		'name'    => esc_html__( 'Fallback Behavior', 'rank-math' ),
		'desc'    => wp_kses_post( __( 'If nothing similar is found, this behavior will be applied. <strong>Note</strong>: If the requested URL ends with <code>/login</code>, <code>/admin</code>, or <code>/dashboard</code>, WordPress will automatically redirect to respective locations within the WordPress admin area.', 'rank-math' ) ),
		'options' => [
			'default'  => esc_html__( 'Default 404', 'rank-math' ),
			'homepage' => esc_html__( 'Redirect to Homepage', 'rank-math' ),
			'custom'   => esc_html__( 'Custom Redirection', 'rank-math' ),
		],
		'default' => 'default',
	]
);

$cmb->add_field(
	[
		'id'   => 'redirections_custom_url',
		'type' => 'text',
		'name' => esc_html__( 'Custom Url ', 'rank-math' ),
		'dep'  => [ [ 'redirections_fallback', 'custom' ] ],
	]
);

$cmb->add_field(
	[
		'id'      => 'redirections_header_code',
		'type'    => 'select',
		'name'    => esc_html__( 'Redirection Type', 'rank-math' ),
		'options' => Helper::choices_redirection_types(),
		'default' => '301',
	]
);

$cmb->add_field(
	[
		'id'      => 'redirections_post_redirect',
		'type'    => 'toggle',
		'name'    => esc_html__( 'Auto Post Redirect', 'rank-math' ),
		'desc'    => esc_html__( 'Extend the functionality of WordPress by creating redirects in our plugin when you change the slug of a post, page, category or a CPT. You can modify the redirection further according to your needs.', 'rank-math' ),
		'default' => 'off',
	]
);
