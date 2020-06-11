<?php
/**
 * Metabox - Local Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$local = [ [ 'rank_math_rich_snippet', 'local,restaurant' ] ];

$cmb->add_field([
	'id'   => 'rank_math_snippet_local_address',
	'type' => 'address',
	'name' => esc_html__( 'Address', 'rank-math' ),
	'dep'  => $local,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_local_geo',
	'type'    => 'text',
	'name'    => esc_html__( 'Geo Coordinates', 'rank-math' ),
	'classes' => 'cmb-row-33',
	'dep'     => $local,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_local_phone',
	'type'    => 'text',
	'name'    => esc_html__( 'Phone Number', 'rank-math' ),
	'classes' => 'cmb-row-33',
	'dep'     => $local,
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_local_price_range',
	'type'       => 'text',
	'name'       => esc_html__( 'Price Range', 'rank-math' ),
	'classes'    => 'cmb-row-33 rank-math-validate-field',
	'attributes' => [
		'data-rule-regex'       => 'true',
		'data-validate-pattern' => '^\${1,4}$',
		'data-msg-regex'        => esc_html__( 'Insert $ / $$ / $$$ / $$$$.', 'rank-math' ),
	],
	'dep'        => $local,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_local_opens',
	'type'    => 'text_time',
	'name'    => esc_html__( 'Opening Time', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $local,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_local_closes',
	'type'    => 'text_time',
	'name'    => esc_html__( 'Closing Time', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $local,
]);

$cmb->add_field([
	'id'                => 'rank_math_snippet_local_opendays',
	'type'              => 'multicheck_inline',
	'name'              => esc_html__( 'Open Days', 'rank-math' ),
	'options'           => [
		'monday'    => esc_html__( 'Monday', 'rank-math' ),
		'tuesday'   => esc_html__( 'Tuesday', 'rank-math' ),
		'wednesday' => esc_html__( 'Wednesday', 'rank-math' ),
		'thursday'  => esc_html__( 'Thursday', 'rank-math' ),
		'friday'    => esc_html__( 'Friday', 'rank-math' ),
		'saturday'  => esc_html__( 'Saturday', 'rank-math' ),
		'sunday'    => esc_html__( 'Sunday', 'rank-math' ),
	],
	'select_all_button' => false,
	'dep'               => $local,
]);
