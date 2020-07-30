<?php
/**
 * Load variables and include view files.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

namespace RankMath;

use RankMath\Helper;

if ( Rollback_Version::should_rollback() ) {
	$rollback = new Rollback_Version();
	$rollback->rollback();
	return;
}

$directory         = dirname( __FILE__ );
$beta_optin        = boolval( Helper::get_settings( 'general.beta_optin' ) );
$auto_update       = boolval( Helper::get_auto_update_setting() );
$auto_update_email = boolval( Helper::get_settings( 'general.enable_auto_update_email' ) );
$versions          = array_reverse( array_keys( Beta_Optin::get_available_versions( $beta_optin ) ) );
$current_version   = rank_math()->version;
$latest_version    = Beta_Optin::get_latest_version();
array_splice( $versions, 10 );

include_once( $directory . '/views/version-control-panel.php' );
include_once( $directory . '/views/beta-optin-panel.php' );
include_once( $directory . '/views/auto-update-panel.php' );
