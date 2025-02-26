<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.239.
 *
 * @since      1.0.239
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\KB;
use RankMath\Helper;
use RankMath\Helpers\Str;
use RankMath\Google\Analytics;

defined( 'ABSPATH' ) || exit;

/**
 * Displays an admin notice to users with a Universal Analytics (UA) property, informing them to update to Google Analytics 4 (GA4).
 */
function rank_math_1_0_239_force_to_connect_ga4() {
	if ( ! Analytics::is_analytics_connected() ) {
		return;
	}

	$analytics = get_option( 'rank_math_google_analytic_options' );

	if ( ! isset( $analytics['property_id'] ) || ! Str::starts_with( 'UA-', $analytics['property_id'] ) ) {
		return;
	}

	Helper::add_notification(
		sprintf(
			// Translators: placeholders are opening and closing tags for connect analytics setting and using ga4 property doc.
			__( 'Universal Analytics (UA) is no longer supported. Please connect your Google Analytics GA4 account by navigating to %1$sGeneral Settings → Analytics%2$s. For more details, refer to this guide: %3$sHow to Use Google Analytics 4 (GA4) Property with Rank Math%4$s.', 'rank-math' ),
			'<a href="' . esc_url( Helper::get_admin_url( 'options-general#setting-panel-analytics' ) ) . '">',
			'</a>',
			'<a href="' . KB::get( 'using-ga4', 'Analytics GA4 KB' ) . '" target="_blank">',
			'</a>'
		),
		[
			'id'      => 'upgrade-ua-to-ga4',
			'type'    => 'error',
			'classes' => 'is-dismissible',
		]
	);
}

rank_math_1_0_239_force_to_connect_ga4();
