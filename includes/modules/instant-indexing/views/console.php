<?php
/**
 * Bing Submission Options.
 *
 * @since   1.0.56
 * @package Rank_Math
 */

defined( 'ABSPATH' ) || exit;

use RankMath\Instant_Indexing\Instant_Indexing;

if ( Instant_Indexing::is_configured() ) {
	$cmb->add_field(
		[
			'id'      => 'bing_api_description',
			'type'    => 'raw',
			/* translators: daily quota */
			'content' => '<div class="bing-api-description description"><p>' . esc_html__( 'Insert URLs to send to the Bing URL Submission API (one per line, up to 500):', 'rank-math' ) . '</p></div>',
		]
	);

	$cmb->add_field(
		[
			'id'              => 'bing_instant_indexing_urls',
			'type'            => 'textarea_small',
			'name'            => esc_html__( 'URL Submission', 'rank-math' ),
			'sanitization_cb' => '__return_false',
			'attributes'      => [
				'class' => 'instant-indexing-urls',
			],
			'after_field'     => '<a href="#" id="bing_api_submit" class="button disabled" style="margin-top: 20px;">' . esc_html__( 'Submit URLs', 'rank-math' ) . '</a> <span class="spinner is-active" id="bing_api_spinner"></span>',
		]
	);

	$rank_math_bing_quota = '[...]';
	$cmb->add_field(
		[
			'id'      => 'bing_api_quota',
			'type'    => 'raw',
			/* translators: daily quota */
			'content' => sprintf( '<div class="bing-api-limit"><p>' . esc_html__( 'Quota Left Today: %s', 'rank-math' ) . '</p></div>', '<strong id="bing_api_limit">' . $rank_math_bing_quota . '</strong>' ),
		]
	);

	return;
}

$cmb->add_field(
	[
		'id'      => 'bing_api_quota',
		'type'    => 'raw',
		/* translators: daily quota */
		'content' => '<p class="bing-api-limit bing-api-limit-unavailable"><span class="dashicons dashicons-arrow-left-alt"></span> ' . esc_html__( 'Please configure the Instant Indexing module in the Settings tab first.', 'rank-math' ) . '</p>',
	]
);
