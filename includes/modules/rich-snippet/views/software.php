<?php
/**
 * Metabox - Software Application Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$software = [ [ 'rank_math_rich_snippet', 'software' ] ];

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_software_price',
		'type'            => 'text',
		'name'            => esc_html__( 'Price', 'rank-math' ),
		'dep'             => $software,
		'classes'         => 'cmb-row-50',
		'attributes'      => [
			'type' => 'number',
			'step' => 'any',
		],
		'escape_cb'       => [ '\RankMath\CMB2', 'sanitize_float' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_float' ],
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_software_price_currency',
		'type'       => 'text',
		'name'       => esc_html__( 'Price Currency', 'rank-math' ),
		'desc'       => esc_html__( 'ISO 4217 Currency code. Example: EUR', 'rank-math' ),
		'classes'    => 'cmb-row-50 rank-math-validate-field',
		'attributes' => [
			'data-rule-regex'       => 'true',
			'data-validate-pattern' => '^[A-Z]{3}$',
			'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: EUR', 'rank-math' ),
		],
		'dep'        => $software,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_software_operating_system',
		'name'    => esc_html__( 'Operating System', 'rank-math' ),
		'type'    => 'text',
		'desc'    => esc_html__( 'For example, "Windows 7", "OSX 10.6", "Android 1.6"', 'rank-math' ),
		'classes' => 'cmb-row-50',
		'dep'     => $software,
	]
);

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_software_application_category',
		'name'    => esc_html__( 'Application Category', 'rank-math' ),
		'type'    => 'text',
		'desc'    => esc_html__( 'For example, "Game", "Multimedia"', 'rank-math' ),
		'classes' => 'cmb-row-50',
		'dep'     => $software,
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_software_rating',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating', 'rank-math' ),
		'desc'            => esc_html__( 'Rating score of the software. Optional.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'dep'             => $software,
		'escape_cb'       => [ '\RankMath\CMB2', 'sanitize_float' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_float' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_software_rating_min',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Minimum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating minimum score of the software.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 1,
		'dep'             => $software,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_software_rating_max',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Maximum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating maximum score of the software.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 5,
		'dep'             => $software,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);
