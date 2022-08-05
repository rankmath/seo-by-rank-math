<?php
/**
 * IndexNow Options: Console tab.
 *
 * @since   1.0.56
 * @package Rank_Math
 */

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'indexnow_description',
		'type'    => 'raw',
		'content' => '<div class="bing-api-description description"><p>' . esc_html__( 'Insert URLs to send to the IndexNow API (one per line, up to 10,000):', 'rank-math' ) . '</p></div>',
	]
);

$cmb->add_field(
	[
		'id'              => 'indexnow_urls',
		'type'            => 'textarea_small',
		'sanitization_cb' => '__return_false',
		'attributes'      => [
			'class' => 'instant-indexing-urls',
			'placeholder' => trailingslashit( home_url() ) . _x( 'hello-world', 'URL slug placeholder', 'rank-math' ),
		],
		'after_field'     => '<a href="#" id="indexnow_submit" class="button button-primary large-button" style="margin-top: 20px;">' . esc_html__( 'Submit URLs', 'rank-math' ) . '</a> <span class="spinner" id="indexnow_spinner"></span>',
	]
);
