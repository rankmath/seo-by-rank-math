<?php
/**
 * The Updates routine for version 1.0.42
 *
 * @since      1.0.42
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use MyThemeShop\Database\Database;
use RankMath\Redirections\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Set Elementor Library Add metabox value to false.
 */
function rank_math_1_0_42_delete_fake_redirection() {
	global $wpdb;

	$table = Database::table( 'rank_math_redirections' );
	$table->select( 'id' )
		->whereLike( 'url_to', 'https://' . 'ischeck' . '.xyz', '' )
		->orWhereLike( 'url_to', 'http://' . 'ischeck' . '.xyz', '' )
		->orWhereLike( 'url_to', '//ischeck' . '.xyz', '' );

	$redirections = $table->get( ARRAY_A );
	$redirections = wp_list_pluck( $redirections, 'id' );

	if ( ! empty( $redirections ) ) {
		DB::delete( $redirections );
	}
}
rank_math_1_0_42_delete_fake_redirection();
