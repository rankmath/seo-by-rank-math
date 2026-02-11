<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.264
 *
 * @since      1.0.264
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

use RankMath\Helpers\DB;

/**
 * Add composite index on status and updated columns to rank_math_redirections table.
 */
function rank_math_1_0_264_add_redirections_index() {
	$index_name = 'idx_rm_status_updated';
	$table_name = 'rank_math_redirections';
	$columns    = [ 'status', 'updated' ];

	if ( DB::check_table_exists( 'rank_math_redirections' ) ) {
		DB::create_index( $index_name, $table_name, $columns );
	}
}

/**
 * Add composite index to rank_math_redirections table for performance optimization.
 */
function rank_math_1_0_264_create_redirections_index() {
	if ( ! is_multisite() ) {
		rank_math_1_0_264_add_redirections_index();
		return;
	}

	$site_ids = get_sites(
		[
			'fields'   => 'ids',
			'number'   => 0,
			'archived' => false,
			'deleted'  => false,
			'spam'     => false,
		]
	);

	foreach ( $site_ids as $site_id ) {
		switch_to_blog( $site_id );
		rank_math_1_0_264_add_redirections_index();
		restore_current_blog();
	}
}
rank_math_1_0_264_create_redirections_index();
