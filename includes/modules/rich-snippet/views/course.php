<?php
/**
 * Metabox - Course Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$course_dep = [ [ 'rank_math_rich_snippet', 'course' ] ];

$cmb->add_field(
	[
		'id'      => 'rank_math_snippet_course_provider_type',
		'type'    => 'radio_inline',
		'name'    => esc_html__( 'Course Provider', 'rank-math' ),
		'options' => [
			'Organization' => esc_html__( 'Organization', 'rank-math' ),
			'Person'       => esc_html__( 'Person', 'rank-math' ),
		],
		'classes' => 'cmb-row-33 nob',
		'default' => 'Organization',
		'dep'     => $course_dep,
	]
);

$cmb->add_field(
	[
		'id'   => 'rank_math_snippet_course_provider',
		'type' => 'text',
		'name' => esc_html__( 'Course Provider Name', 'rank-math' ),
		'dep'  => $course_dep,
	]
);

$cmb->add_field(
	[
		'id'         => 'rank_math_snippet_course_provider_url',
		'type'       => 'text_url',
		'name'       => esc_html__( 'Course Provider URL', 'rank-math' ),
		'dep'        => $course_dep,
		'attributes' => [
			'data-rule-url' => 'true',
		],
		'classes'    => 'nob rank-math-validate-field',
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_course_rating',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating', 'rank-math' ),
		'desc'            => esc_html__( 'Rating score of the course. Optional.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'dep'             => $course_dep,
		'escape_cb'       => [ '\RankMath\CMB2', 'sanitize_float' ],
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_float' ],
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_course_rating_min',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Minimum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating minimum score of the course.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 1,
		'dep'             => $course_dep,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);

$cmb->add_field(
	[
		'id'              => 'rank_math_snippet_course_rating_max',
		'type'            => 'text',
		'name'            => esc_html__( 'Rating Maximum', 'rank-math' ),
		'desc'            => esc_html__( 'Rating maximum score of the course.', 'rank-math' ),
		'classes'         => 'cmb-row-33',
		'default'         => 5,
		'dep'             => $course_dep,
		'escape_cb'       => 'absint',
		'sanitization_cb' => 'absint',
	]
);
