<?php
/**
 * The Updates routine for version 1.0.50
 *
 * @since      1.0.50
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;

/**
 * Enable the new Analytis module
 */
function rank_math_1_0_50_enable_new_analytics_module() {
	$active_modules = get_option( 'rank_math_modules', [] );
	if ( is_array( $active_modules ) && in_array( 'search-console', $active_modules, true ) ) {
		Helper::update_modules( [ 'analytics' => 'on' ] );
		\RankMath\Analytics\Data_Fetcher::get()->flat_posts();
		rank_math_1_0_50_reconnect_sc_notification();
	}
	( new \RankMath\Analytics\Installer() )->install();
}

/**
 * Sync the user roles with new module.
 */
function rank_math_1_0_50_sync_user_roles() {
	global $wpdb;
	$users = get_users(
		[
			'meta_query' => [
				[
					'key'     => $wpdb->get_blog_prefix() . 'capabilities',
					'value'   => '%search_console%',
					'compare' => 'LIKE',
				],
			],
		]
	);

	foreach ( $users as $user ) {
		$user->add_cap( 'rank_math_analytics' );
	}
}

/**
 * Show notice to re-connect if Search Console was previously connected.
 *
 * @return bool
 */
function rank_math_1_0_50_reconnect_sc_notification() {
	$key    = 'rank_math_search_console_data';
	$option = get_option( $key, [] );
	if ( ! is_array( $option ) || empty( $option['authorized'] ) ) {
		return;
	}

	Helper::add_notification(
		sprintf(
			// Translators: placeholders are the opening and closing anchor tags.
			esc_html__( 'Thank you for updating Rank Math! We\'ve completely revamped our Analytics module with Google Analytics & Google Search Console integrations. For a seamless experience, please re-authenticate your Google account. %1$sClick here to Connect%2$s', 'rank-math' ),
			'<div style="margin-top: 10px;"><a href="' . Helper::get_admin_url( 'options-general#setting-panel-analytics' ) . '" class="button button-primary">',
			'</a></div>'
		),
		[
			'type' => 'warning',
			'id'   => 'rank_math_analytics_reauthenticate',
		]
	);
}

rank_math_1_0_50_sync_user_roles();
rank_math_1_0_50_enable_new_analytics_module();
