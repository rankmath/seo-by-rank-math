<?php
/**
 * The Updates routine for version 1.0.43
 *
 * @since      1.0.43
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

/**
 * Enable the new Image SEO module if either the "add_img_alt" or the
 * "add_img_title" is being used.
 */
function rank_math_1_0_43_maybe_enable_image_seo_module() {
	if ( Helper::get_settings( 'general.add_img_alt' ) || Helper::get_settings( 'general.add_img_title' ) ) {
		Helper::update_modules( [ 'image-seo' => 'on' ] );
	}
}
rank_math_1_0_43_maybe_enable_image_seo_module();

/**
 * Update setup mode on existing sites.
 */
function rank_math_1_0_43_update_setup_mode() {
	$all_opts              = rank_math()->settings->all_raw();
	$general               = $all_opts['general'];
	$general['setup_mode'] = 'advanced';

	Helper::update_all_settings( $general, null, null );
	rank_math()->settings->reset();
}
rank_math_1_0_43_update_setup_mode();

/**
 * Encrypt sensitive data.
 */
function rank_math_1_0_43_encrypt_sensitive_data() {
	// Get unencrypted Rank Math Account data.
	$rank_math_data = get_option( 'rank_math_connect_data', false );
	if ( $rank_math_data ) {
		// Re-save to encrypt.
		Admin_Helper::get_registration_data( $rank_math_data );
	}

	// Get unencrypted Search Console data.
	$search_console_data = get_option( 'rank_math_search_console_data', false );
	if ( $search_console_data ) {
		// Re-save to encrypt.
		Helper::search_console_data( $search_console_data );
	}
}
rank_math_1_0_43_encrypt_sensitive_data();
