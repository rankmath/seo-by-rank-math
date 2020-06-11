<?php
/**
 * Metabox - Restaurant Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$restaurant = [ [ 'rank_math_rich_snippet', 'restaurant' ] ];

$cmb->add_field([
	'id'      => 'rank_math_snippet_restaurant_serves_cuisine',
	'type'    => 'text',
	'name'    => esc_html__( 'Serves Cuisine', 'rank-math' ),
	'desc'    => esc_html__( 'The type of cuisine we serve. Separated by comma.', 'rank-math' ),
	'classes' => 'cmb-row-50 nob',
	'dep'     => $restaurant,
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_restaurant_menu',
	'type'       => 'text_url',
	'name'       => esc_html__( 'Menu URL', 'rank-math' ),
	'desc'       => esc_html__( 'URL pointing to the menu of the restaurant.', 'rank-math' ),
	'classes'    => 'cmb-row-33 nob rank-math-validate-field',
	'attributes' => [
		'data-rule-url' => true,
	],
	'dep'        => $restaurant,
]);
