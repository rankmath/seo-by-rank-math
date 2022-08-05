<?php
/**
 * IndexNow Options: History tab.
 *
 * @since   1.0.56
 * @package Rank_Math
 */

defined( 'ABSPATH' ) || exit;

$history_content  = '';
$history_content .= '<a href="#" id="indexnow_clear_history" class="button alignright hidden">' . esc_html__( 'Clear History', 'rank-math' ) . '</a>';
$history_content .= '<div class="history-filter-links hidden" id="indexnow_history_filters"><a href="#" data-filter="all" class="current">' . esc_html__( 'All', 'rank-math' ) . '</a> | <a href="#" data-filter="manual">' . esc_html__( 'Manual', 'rank-math' ) . '</a> | <a href="#" data-filter="auto">' . esc_html__( 'Auto', 'rank-math' ) . '</a></div>';
$history_content .= '<div class="clear"></div>';
$history_content .= '<table class="wp-list-table widefat striped" id="indexnow_history"><thead><tr><th class="col-date">' . esc_html__( 'Time', 'rank-math' ) . '</th><th class="col-url">' . esc_html__( 'URL', 'rank-math' ) . '</th><th class="col-status">' . esc_html__( 'Response', 'rank-math' ) . '</th></tr></thead><tbody>';
$history_content .= '</tbody></table>';

$cmb->add_field(
	[
		'id'      => 'indexnow_history',
		'type'    => 'raw',
		/* translators: daily quota */
		'content' => $history_content,
	]
);

$help_contents = '';

$help_contents .= '<a href="#" id="indexnow_show_response_codes">' . esc_html__( 'Response Code Help', 'rank-math' ) . '<span class="dashicons dashicons-arrow-down"></span></a>';

$help_contents .= '<table class="wp-list-table widefat striped hidden" id="indexnow_response_codes"><thead><tr><th class="col-response-code">' . esc_html__( 'Response Code', 'rank-math' ) . '</th><th class="col-response-message">' . esc_html__( 'Response Message', 'rank-math' ) . '</th><th class="col-reasons">' . esc_html__( 'Reasons', 'rank-math' ) . '</th></tr></thead><tbody>';
$help_contents .= '<tr><td class="col-response-code">200</td><td class="col-response-message">' . esc_html__( 'OK', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'The URL was successfully submitted to the IndexNow API.', 'rank-math' ) . '</td></tr>';
$help_contents .= '<tr><td class="col-response-code">202</td><td class="col-response-message">' . esc_html__( 'Accepted', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'The URL was successfully submitted to the IndexNow API, but the API key will be checked later.', 'rank-math' ) . '</td></tr>';
$help_contents .= '<tr><td class="col-response-code">400</td><td class="col-response-message">' . esc_html__( 'Bad Request', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'The request was invalid.', 'rank-math' ) . '</td></tr>';
$help_contents .= '<tr><td class="col-response-code">403</td><td class="col-response-message">' . esc_html__( 'Forbidden', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'The key was invalid (e.g. key not found, file found but key not in the file).', 'rank-math' ) . '</td></tr>';
$help_contents .= '<tr><td class="col-response-code">422</td><td class="col-response-message">' . esc_html__( 'Unprocessable Entity', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'The URLs don\'t belong to the host or the key is not matching the schema in the protocol.', 'rank-math' ) . '</td></tr>';
$help_contents .= '<tr><td class="col-response-code">429</td><td class="col-response-message">' . esc_html__( 'Too Many Requests', 'rank-math' ) . '</td><td class="col-reasons">' . esc_html__( 'Too Many Requests (potential Spam).', 'rank-math' ) . '</td></tr>';
$help_contents .= '</tbody></table>';

$cmb->add_field(
	[
		'id'      => 'indexnow_help',
		'type'    => 'raw',
		/* translators: daily quota */
		'content' => $help_contents,
	]
);
