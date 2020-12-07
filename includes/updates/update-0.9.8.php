<?php
/**
 * The Updates routine for version 0.9.8.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

use MyThemeShop\Helpers\DB;
use RankMath\Redirections\DB as Redirections_DB;

defined( 'ABSPATH' ) || exit;

/**
 * Create and update table schema
 *
 * @since 1.0.0
 */
function rank_math_0_9_8_update_tables() {
	global $wpdb;
	require_once ABSPATH . 'wp-admin/includes/upgrade.php';

	$max_index_length = 191;
	$redirections     = [];
	$charset_collate  = $wpdb->get_charset_collate();

	// Rename old tables.
	if ( DB::check_table_exists( 'rank_math_redirections' ) ) {
		$redirections = DB::query_builder( 'rank_math_redirections' )->get( ARRAY_A );
		$wpdb->query( "ALTER TABLE {$wpdb->prefix}rank_math_redirections RENAME TO {$wpdb->prefix}rank_math_redirections_old;" ); // phpcs:ignore
	}

	// Create new tables.
	$sql = "CREATE TABLE {$wpdb->prefix}rank_math_redirections (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		url_to TEXT NOT NULL,
		header_code SMALLINT(4) UNSIGNED NOT NULL,
		times_accessed BIGINT(20) UNSIGNED NOT NULL DEFAULT '0',
		last_accessed DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		last_edit DATETIME NOT NULL DEFAULT '0000-00-00 00:00:00',
		redirection_status VARCHAR(20) NOT NULL DEFAULT 'active',
		redirection_condition VARCHAR(32) NOT NULL DEFAULT 'none',
		author BIGINT(20) UNSIGNED NOT NULL,
		linked_object VARCHAR(16) NOT NULL DEFAULT '',
		PRIMARY KEY (id),
		KEY (redirection_status),
		KEY (redirection_condition)
	) $charset_collate;";
	dbDelta( $sql );

	$sql = "CREATE TABLE {$wpdb->prefix}rank_math_redirection_sources (
		id BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
		redirection_id BIGINT(20) UNSIGNED NOT NULL,
		pattern VARCHAR(255)  NOT NULL,
		comparison VARCHAR(32) NOT NULL,
		PRIMARY KEY (id),
		KEY pattern (pattern($max_index_length))
	) $charset_collate;";

	dbDelta( $sql );

	if ( empty( $redirections ) ) {
		return;
	}

	foreach ( $redirections as $redirection ) {
		$sources                 = [];
		$redirection['url_from'] = maybe_unserialize( $redirection['url_from'] );
		foreach ( $redirection['url_from'] as $url_from ) {
			$sources[] = [
				'pattern'    => $url_from['url'],
				'comparison' => $url_from['comparison'],
			];
		}

		$status = 'active';
		$value  = intval( $redirection['is_active'] );
		if ( -1 === $value ) {
			$status = 'trashed';
		} elseif ( 0 === $value ) {
			$status = 'inactive';
		}

		$data = [
			'url_to'                => $redirection['url_to'],
			'header_code'           => $redirection['header_code'],
			'times_accessed'        => '0',
			'last_accessed'         => $redirection['last_accessed'],
			'last_edit'             => current_time( 'mysql' ),
			'redirection_status'    => $status,
			'redirection_condition' => 'none',
			'author'                => 0,
			'linked_object'         => '',
			'sources'               => $sources,
		];

		Redirections_DB::add( $data );
	}
}

rank_math_0_9_8_update_tables();
