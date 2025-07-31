<?php
/**
 * The llms.txt settings.
 *
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'llms_info',
		'type'    => 'notice',
		'what'    => 'info',
		'classes' => 'nob nopt rank-math-notice',
		'content' => sprintf(
			// Translators: placeholder is the llms.txt file URL.
			__( 'Your <code>llms.txt</code> file is available at <a href="%1$s" target="_blank">%2$s</a>.', 'rank-math' ),
			esc_url( home_url( '/llms.txt' ) ),
			esc_html( home_url( '/llms.txt' ) )
		),
	]
);

$post_types = Helper::choices_post_types();
if ( isset( $post_types['attachment'] ) ) {
	unset( $post_types['attachment'] );
}

$cmb->add_field(
	[
		'id'      => 'llms_post_types',
		'name'    => esc_html__( 'Select Post Types', 'rank-math' ),
		'desc'    => esc_html__( 'Select the post types to be included in the llms.txt file.', 'rank-math' ),
		'type'    => 'multicheck',
		'options' => $post_types,
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'      => 'llms_taxonomies',
		'name'    => esc_html__( 'Select Taxonomies', 'rank-math' ),
		'desc'    => esc_html__( 'Select the taxonomies to be included in the llms.txt file.', 'rank-math' ),
		'type'    => 'multicheck',
		'options' => wp_list_pluck( Helper::get_accessible_taxonomies(), 'label', 'name' ),
		'classes' => 'rank-math-advanced-option',
	]
);

$cmb->add_field(
	[
		'id'         => 'llms_limit',
		'name'       => esc_html__( 'Posts/Terms Limit', 'rank-math' ),
		'desc'       => esc_html__( 'Maximum number of links to include for each post type.', 'rank-math' ),
		'type'       => 'text',
		'classes'    => 'small-text',
		'default'    => 50,
		'attributes' => [
			'type' => 'number',
			'min'  => 1,
		],
	]
);

$cmb->add_field(
	[
		'id'         => 'llms_extra_content',
		'name'       => esc_html__( 'Additional Content', 'rank-math' ),
		'desc'       => esc_html__( 'Add any extra text or links you\'d like to include in your llms.txt file manually.', 'rank-math' ),
		'type'       => 'textarea',
		'attributes' => [ 'rows' => 6 ],
	]
);
