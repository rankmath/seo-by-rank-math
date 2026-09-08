<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.277.2
 *
 * @since      1.0.277.2
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Delete WordPress Application Passwords minted by the Support Agent WAP integration.
 */
function rank_math_1_0_277_2_delete_wap_application_passwords() {
	global $wpdb;

	if ( ! class_exists( 'WP_Application_Passwords' ) ) {
		return;
	}

	$password_name = 'WAP – Rank Math Support Agent';

	// Narrows to only usermeta rows that can contain the password, so the
	// LIKE scan doesn't touch unrelated meta on large sites.
	$user_ids = $wpdb->get_col(
		$wpdb->prepare(
			"SELECT DISTINCT user_id FROM %i WHERE meta_key = '_application_passwords' AND meta_value LIKE %s",
			$wpdb->usermeta,
			'%' . $wpdb->esc_like( $password_name ) . '%'
		)
	);

	foreach ( $user_ids as $user_id ) {
		$passwords = WP_Application_Passwords::get_user_application_passwords( $user_id );
		foreach ( $passwords as $password ) {
			if ( $password_name === $password['name'] ) {
				WP_Application_Passwords::delete_application_password( $user_id, $password['uuid'] );
			}
		}
	}
}
rank_math_1_0_277_2_delete_wap_application_passwords();
