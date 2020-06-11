<?php
/**
 * Metabox - Review Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$review = [ [ 'rank_math_rich_snippet', 'review' ] ];

$cmb->add_field([
	'id'         => 'rank_math_snippet_review_worst_rating',
	'name'       => esc_html__( 'Worst Rating', 'rank-math' ),
	'desc'       => esc_html__( 'Minimum rating.', 'rank-math' ),
	'type'       => 'text',
	'default'    => 1,
	'dep'        => $review,
	'classes'    => 'cmb-row-33',
	'attributes' => [ 'type' => 'number' ],
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_review_best_rating',
	'name'       => esc_html__( 'Best Rating', 'rank-math' ),
	'desc'       => esc_html__( 'Maximum rating.', 'rank-math' ),
	'type'       => 'text',
	'default'    => 5,
	'dep'        => $review,
	'classes'    => 'cmb-row-33',
	'attributes' => [ 'type' => 'number' ],
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_review_rating_value',
	'name'       => esc_html__( 'Rating', 'rank-math' ),
	'desc'       => esc_html__( 'Final rating of the item.', 'rank-math' ),
	'type'       => 'text',
	'dep'        => $review,
	'classes'    => 'cmb-row-33',
	'attributes' => [
		'type' => 'number',
		'step' => 'any',
	],
]);

$cmb->add_field([
	'id'      => 'rank_math_snippet_review_location',
	'name'    => esc_html__( 'Review Location', 'rank-math' ),
	'desc'    => esc_html__( 'The review or rating must be displayed on the page to comply with Google\'s Rich Snippet guidelines.', 'rank-math' ),
	'type'    => 'select',
	'dep'     => $review,
	'classes' => 'nob',
	'default' => 'bottom',
	'options' => [
		'bottom' => esc_html__( 'Below Content', 'rank-math' ),
		'top'    => esc_html__( 'Above Content', 'rank-math' ),
		'both'   => esc_html__( 'Above & Below Content', 'rank-math' ),
		'custom' => esc_html__( 'Custom (use shortcode)', 'rank-math' ),
	],
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_review_shortcode',
	'name'       => ' ',
	'type'       => 'text',
	'classes'    => 'nopt',
	'save_field' => false,
	'desc'       => esc_html__( 'Copy & paste this shortcode in the content.', 'rank-math' ),
	'dep'        => [
		'relation' => 'and',
		[ 'rank_math_rich_snippet', 'review' ],
		[ 'rank_math_snippet_review_location', 'custom' ],
	],
	'attributes' => [
		'readonly' => 'readonly',
		'value'    => '[rank_math_review_snippet]',
	],
]);
