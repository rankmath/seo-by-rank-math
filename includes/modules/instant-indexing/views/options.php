<?php
/**
 * IndexNow Settings.
 *
 * @since      1.0.56
 * @package    RankMath
 * @subpackage RankMath\Settings
 */

namespace RankMath\Instant_Indexing;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$cmb->add_field(
	[
		'id'      => 'bing_post_types',
		'type'    => 'multicheck',
		'name'    => esc_html__( 'Auto-Submit Post Types', 'rank-math' ),
		'desc'    => esc_html__( 'Submit posts from these post types automatically to the IndexNow API when a post is published, updated, or trashed.', 'rank-math' ),
		'options' => Helper::choices_post_types(),
	]
);

$cmb->add_field(
	[
		'id'          => 'indexnow_api_key',
		'name'        => esc_html__( 'API Key', 'rank-math' ),
		'desc'        => esc_html__( 'The IndexNow API key proves the ownership of the site. It is generated automatically. You can change the key if it becomes known to third parties.', 'rank-math' ),
		'type'        => 'text',
		'after_field' => '<a href="#" id="indexnow_reset_key" class="button button-secondary large-button"><span class="dashicons dashicons-update"></span> ' . esc_html__( 'Change Key', 'rank-math' ) . '</a>',
		'classes'     => 'rank-math-advanced-option',
		'attributes'  => [
			'readonly' => 'readonly',
		],
	]
);

$key_location    = Api::get()->get_key_location( 'settings_field' );
$field_label     = esc_html__( 'API Key Location', 'rank-math' );
$check_key_label = esc_html__( 'Check Key', 'rank-math' );

// Translators: %s is the words "Check Key".
$field_desc = sprintf( esc_html__( 'Use the %1$s button to verify that the key is accessible for search engines. Clicking on it should open the key file in your browser and show the API key.', 'rank-math' ), '<strong>' . $check_key_label . '</strong>' );

$location_field = '<div class="cmb-row cmb-type-text cmb2-id-indexnow-api-key-location table-layout rank-math-advanced-option" data-fieldtype="text">
<div class="cmb-th">
<label for="indexnow_api_key_location">' . $field_label . '</label>
</div>
	<div class="cmb-td">
<code id="indexnow_api_key_location">' . esc_url( $key_location ) . '</code>
<p class="cmb2-metabox-description">' . $field_desc . '</p>
<a href="' . esc_url( $key_location ) . '" id="indexnow_check_key" class="button button-secondary large-button" target="_blank"><span class="dashicons dashicons-search"></span> ' . $check_key_label . '</a>
	</div>
</div>';

$cmb->add_field(
	[
		'id'      => 'indexnow_api_key_location',
		'type'    => 'raw',
		'content' => $location_field,
	]
);
