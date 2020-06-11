<?php
/**
 * Metabox - Person Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$person = [ [ 'rank_math_rich_snippet', 'person' ] ];

$cmb->add_field([
	'id'         => 'rank_math_snippet_person_email',
	'type'       => 'text',
	'attributes' => [ 'type' => 'email' ],
	'name'       => esc_html__( 'Email', 'rank-math' ),
	'classes'    => 'rank-math-validate-field',
	'attributes' => [
		'data-rule-email' => true,
	],
	'dep'        => $person,
]);

$cmb->add_field([
	'id'   => 'rank_math_snippet_person_address',
	'type' => 'address',
	'name' => esc_html__( 'Address', 'rank-math' ),
	'dep'  => $person,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_person_gender',
	'type'    => 'text',
	'name'    => esc_html__( 'Gender', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $person,
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_person_job_title',
	'type'    => 'text',
	'name'    => esc_html__( 'Job title', 'rank-math' ),
	'desc'    => esc_html__( 'The job title of the person (for example, Financial Manager).', 'rank-math' ),
	'classes' => 'cmb-row-50',
	'dep'     => $person,
]);
