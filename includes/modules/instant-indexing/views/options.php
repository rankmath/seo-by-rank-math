<?php
/**
 * Bing Instant Indexing Settings.
 *
 * @since      1.0.56
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'              => 'bing_api_key',
		'type'            => 'text',
		'name'            => esc_html__( 'Bing API Key', 'rank-math' ),
		/* translators: Link to KB article */
		'desc'            => sprintf( esc_html__( 'Insert your Bing Webmaster Tools API Key. %s', 'rank-math' ), '<a href="' . KB::get( 'bing-instant-indexing' ) . '" target="_blank">' . esc_html__( 'How to obtain it?', 'rank-math' ) . '</a>' ),
		'classes'         => 'large-text',
		'default'         => '',
		'sanitization_cb' => [ '\RankMath\CMB2', 'sanitize_textfield' ],
		'attributes'      => [ 'type' => 'password' ],
	]
);

$cmb->add_field(
	[
		'id'      => 'bing_post_types',
		'type'    => 'multicheck',
		'name'    => esc_html__( 'Auto-Submit to Bing', 'rank-math' ),
		'desc'    => esc_html__( 'Submit posts from these post types automatically to the Bing URL Submission API when a post is published or edited.', 'rank-math' ),
		'options' => Helper::choices_post_types(),
		'default' => [ 'post', 'page' ],
	]
);
