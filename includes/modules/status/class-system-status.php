<?php
/**
 * This class handles the content in Status & Tools > System Status.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;
use RankMath\Helpers\DB as DB_Helper;
use RankMath\Google\Authentication;
use RankMath\Admin\Admin_Helper;
use RankMath\Google\Permissions;

defined( 'ABSPATH' ) || exit;

/**
 * System_Status class.
 */
class System_Status {

	/**
	 * Get localized JSON data based on the Page view.
	 */
	public static function get_json_data() {
		global $wpdb;
		$info = [];

		$plan             = Admin_Helper::get_registration_data();
		$tokens           = Authentication::tokens();
		$modules          = Helper::get_active_modules();
		$permissions      = Permissions::get_status();
		$permissions_data = '';

		if ( ! empty( $permissions ) ) {
			$permissions_data = implode(
				', ',
				array_map(
					fn( $k, $v ) => "$k: $v",
					array_keys( $permissions ),
					$permissions
				)
			);
		}

		$rankmath = [
			'label'  => esc_html__( 'Rank Math', 'rank-math' ),
			'fields' => [
				'version'          => [
					'label' => esc_html__( 'Version', 'rank-math' ),
					'value' => get_option( 'rank_math_version' ),
				],
				'database_version' => [
					'label' => esc_html__( 'Database version', 'rank-math' ),
					'value' => get_option( 'rank_math_db_version' ),
				],
				'plugin_plan'      => [
					'label' => esc_html__( 'Plugin subscription plan', 'rank-math' ),
					'value' => isset( $plan['plan'] ) ? \ucwords( $plan['plan'] ) : esc_html__( 'Free', 'rank-math' ),
				],
				'active_modules'   => [
					'label' => esc_html__( 'Active modules', 'rank-math' ),
					'value' => empty( $modules ) ? esc_html__( '(none)', 'rank-math' ) : join( ', ', $modules ),
				],
				'refresh_token'    => [
					'label' => esc_html__( 'Google Refresh token', 'rank-math' ),
					'value' => empty( $tokens['refresh_token'] ) ? esc_html__( 'No token', 'rank-math' ) : esc_html__( 'Token exists', 'rank-math' ),
				],
				'permissions'      => [
					'label' => esc_html__( 'Google Permission', 'rank-math' ),
					'value' => $permissions_data,
				],
			],
		];

		$database_tables = DB_Helper::get_results(
			$wpdb->prepare(
				"SELECT
				table_name AS 'name'
				FROM information_schema.TABLES
				WHERE table_schema = %s
				AND table_name LIKE %s
				ORDER BY name ASC;",
				DB_NAME,
				'%rank\\_math%'
			)
		);

		$tables = [];
		foreach ( $database_tables as $table ) {
			$name            = \str_replace( $wpdb->prefix, '', $table->name );
			$tables[ $name ] = true;
		}

		$should_exist = [
			'rank_math_404_logs'                  => esc_html__( 'Database Table: 404 Log', 'rank-math' ),
			'rank_math_redirections'              => esc_html__( 'Database Table: Redirection', 'rank-math' ),
			'rank_math_redirections_cache'        => esc_html__( 'Database Table: Redirection Cache', 'rank-math' ),
			'rank_math_internal_links'            => esc_html__( 'Database Table: Internal Link', 'rank-math' ),
			'rank_math_internal_meta'             => esc_html__( 'Database Table: Internal Link Meta', 'rank-math' ),
			'rank_math_analytics_gsc'             => esc_html__( 'Database Table: Google Search Console', 'rank-math' ),
			'rank_math_analytics_objects'         => esc_html__( 'Database Table: Flat Posts', 'rank-math' ),
			'rank_math_analytics_ga'              => esc_html__( 'Database Table: Google Analytics', 'rank-math' ),
			'rank_math_analytics_adsense'         => esc_html__( 'Database Table: Google AdSense', 'rank-math' ),
			'rank_math_analytics_keyword_manager' => esc_html__( 'Database Table: Keyword Manager', 'rank-math' ),
			'rank_math_analytics_inspections'     => esc_html__( 'Database Table: Inspections', 'rank-math' ),
		];

		if ( ! defined( 'RANK_MATH_PRO_FILE' ) ) {
			unset(
				$should_exist['rank_math_analytics_ga'],
				$should_exist['rank_math_analytics_adsense'],
				$should_exist['rank_math_analytics_keyword_manager']
			);
		}

		foreach ( $should_exist as $name => $label ) {
			$rankmath['fields'][ $name ] = [
				'label' => $label,
				'value' => isset( $tables[ $name ] ) ? self::get_table_size( $name ) : esc_html__( 'Not found', 'rank-math' ),
			];
		}

		// Core debug data.
		if ( ! class_exists( 'WP_Debug_Data' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-debug-data.php'; // @phpstan-ignore-line
		}

		if ( ! function_exists( 'got_url_rewrite' ) ) {
			require_once ABSPATH . 'wp-admin/includes/misc.php'; // @phpstan-ignore-line
		}

		if ( false === function_exists( 'get_core_updates' ) ) {
			require_once ABSPATH . 'wp-admin/includes/update.php'; // @phpstan-ignore-line
		}

		if ( ! class_exists( 'WP_Site_Health' ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-site-health.php'; // @phpstan-ignore-line
		}

		$rankmath_data = apply_filters( 'rank_math/status/rank_math_info', $rankmath );
		$core_data     = \WP_Debug_Data::debug_data();

		// Keep only relevant data.
		$core_data = array_intersect_key(
			$core_data,
			array_flip(
				[
					'wp-core',
					'wp-dropins',
					'wp-active-theme',
					'wp-parent-theme',
					'wp-mu-plugins',
					'wp-plugins-active',
					'wp-server',
					'wp-database',
					'wp-constants',
					'wp-filesystem',
				]
			)
		);

		$system_info = [ 'rank-math' => $rankmath_data ] + $core_data;
		return array_merge(
			[
				'systemInfo'     => $system_info,
				'systemInfoCopy' => esc_attr( \WP_Debug_Data::format( $system_info, 'debug' ) ),
			],
			\RankMath\Status\Error_Log::get_error_log_localized_data()
		);
	}

	/**
	 * Get Table size.
	 *
	 * @param string $table Table name.
	 *
	 * @return int Table size.
	 */
	public static function get_table_size( $table ) {
		global $wpdb;
		$size = (int) DB_Helper::get_var( "SELECT SUM((data_length + index_length)) AS size FROM information_schema.TABLES WHERE table_schema='" . $wpdb->dbname . "' AND (table_name='" . $wpdb->prefix . $table . "')" );
		return size_format( $size );
	}
}
