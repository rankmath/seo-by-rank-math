<?php
/**
 * Metabox - Service Rich Snippet
 *
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 */

$service = [ [ 'rank_math_rich_snippet', 'service' ] ];

$cmb->add_field([
	'id'   => 'rank_math_snippet_service_type',
	'name' => esc_html__( 'Service Type', 'rank-math' ),
	'type' => 'text',
	'desc' => esc_html__( 'The type of service being offered, e.g. veterans\' benefits, emergency relief, etc.', 'rank-math' ),
	'dep'  => $service,
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_service_price',
	'type'       => 'text',
	'name'       => esc_html__( 'Price', 'rank-math' ),
	'desc'       => esc_html__( 'Insert price, e.g. "50.00", or a price range, e.g. "40.00-50.00".', 'rank-math' ),
	'classes'    => 'cmb-row-50 rank-math-validate-field',
	'dep'        => $service,
	'attributes' => [
		'data-rule-regex'       => 'true',
		'data-validate-pattern' => '[\d -]+',
		'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: 25', 'rank-math' ),
	],
]);

$cmb->add_field([
	'id'         => 'rank_math_snippet_service_price_currency',
	'type'       => 'text',
	'name'       => esc_html__( 'Price Currency', 'rank-math' ),
	'desc'       => esc_html__( 'ISO 4217 Currency code. Example: EUR', 'rank-math' ),
	'classes'    => 'cmb-row-50 rank-math-validate-field',
	'attributes' => [
		'data-rule-regex'       => 'true',
		'data-validate-pattern' => '^[A-Z]{3}$',
		'data-msg-regex'        => esc_html__( 'Please use the correct format. Example: EUR', 'rank-math' ),
	],
	'dep'        => $service,
]);
