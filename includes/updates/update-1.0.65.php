<?php
/**
 * The Updates routine for version 1.0.65.
 *
 * @since      1.0.65
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Google\Console;

defined( 'ABSPATH' ) || exit;

/**
 * Set defaults for the new options added in this version.
 */
function rank_math_1_0_65_default_options() {
	$all_opts = rank_math()->settings->all_raw();
	$general  = $all_opts['general'];

	// Turn this option off by default after updating.
	$general['console_email_reports'] = 'off';

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_65_default_options();

/**
 * Add admin notice to inform users about the new Email Reports feature.
 */
function rank_math_1_0_65_reports_notice() {
	$active_modules = get_option( 'rank_math_modules', [] );
	if ( ! is_array( $active_modules ) || ! in_array( 'analytics', $active_modules, true ) ) {
		return;
	}

	if ( ! Console::is_console_connected() ) {
		return;
	}

	Helper::add_notification(
		sprintf(
			// Translators: placeholders are the opening and closing anchor tags.
			'<svg style="vertical-align: middle; margin-right: 5px" viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="20"><g><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"></path><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"></path></g></svg><strong>' . esc_html__( 'Rank Math: Introducing SEO Performance Reports via Email. %1$sClick here to enable it%2$s.', 'rank-math' ) . '</strong>',
			'<a href="###ENABLE_EMAIL_REPORTS###">',
			'</a>'
		),
		[
			'type'       => 'warning',
			'id'         => 'rank_math_analytics_new_email_reports',
			'capability' => 'rank_math_analytics',
		]
	);
}
rank_math_1_0_65_reports_notice();

/**
 * Clear scheduled event added in version 1.0.65-beta.
 *
 * @return void
 */
function rank_math_1_0_65_clear_beta_scheduled_event() {
	$event = 'rank_math/analytics/email_report_event';
	$timestamp = wp_next_scheduled( $event );
	if ( $timestamp ) {
		wp_unschedule_event( $timestamp, $event );
	}
}
rank_math_1_0_65_clear_beta_scheduled_event();
