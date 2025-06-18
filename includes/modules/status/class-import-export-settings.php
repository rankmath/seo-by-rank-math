<?php
/**
 * The Import Export Settings Class
 *
 * @since      1.0.240
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use WP_REST_Response;
use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Status\Backup;

defined( 'ABSPATH' ) || exit;

/**
 * Import_Export_Settings class.
 */
class Import_Export_Settings {
	/**
	 * Handle export.
	 */
	public static function export() {
		$panels = Param::post( 'panels', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );

		return wp_json_encode( self::get_export_data( $panels ) );
	}

	/**
	 * Handle import.
	 */
	public static function import() {
		$file = Helper::handle_file_upload();
		if ( isset( $file['error'] ) ) {
			return [
				'error' => esc_html__( 'Settings could not be imported:', 'rank-math' ) . ' ' . $file['error'],
			];
		}

		if ( ! isset( $file['file'] ) ) {
			return [
				'error' => esc_html__( 'Settings could not be imported: Upload failed.', 'rank-math' ),
			];
		}

		// Parse Options.
		$wp_filesystem = Helper::get_filesystem();
		if ( is_null( $wp_filesystem ) || ! Helper::is_filesystem_direct() ) {
			return [
				'error' => esc_html__( 'Uploaded file could not be read.', 'rank-math' ),
			];
		}

		$settings = $wp_filesystem->get_contents( $file['file'] );
		$settings = json_decode( $settings, true );

		\wp_delete_file( $file['file'] );

		if ( is_array( $settings ) && self::do_import_data( $settings ) ) {
			return [
				'success' => esc_html__( 'Settings successfully imported. Your old configuration has been saved as a backup.', 'rank-math' ),
			];
		}

		return [
			'error' => esc_html__( 'No settings found to be imported.', 'rank-math' ),
		];
	}

	/**
	 * Does import data.
	 *
	 * @param  array $data           Import data.
	 * @param  bool  $suppress_hooks Suppress hooks or not.
	 * @return bool
	 */
	public static function do_import_data( array $data, $suppress_hooks = false ) {
		self::run_import_hooks( 'pre_import', $data, $suppress_hooks );
		Backup::create_backup();

		// Import options.
		$down = self::set_options( $data );

		// Import capabilities.
		if ( ! empty( $data['role-manager'] ) ) {
			$down = true;
			Helper::set_capabilities( $data['role-manager'] );
		}

		// Import redirections.
		if ( ! empty( $data['redirections'] ) ) {
			$down = true;
			self::set_redirections( $data['redirections'] );
		}

		self::run_import_hooks( 'after_import', $data, $suppress_hooks );

		return $down;
	}

	/**
	 * Set options from data.
	 *
	 * @param array $data An array of data.
	 */
	private static function set_options( $data ) {
		$set  = false;
		$hash = [
			'modules' => 'rank_math_modules',
			'general' => 'rank-math-options-general',
			'titles'  => 'rank-math-options-titles',
			'sitemap' => 'rank-math-options-sitemap',
		];

		foreach ( $hash as $key => $option_key ) {
			if ( ! empty( $data[ $key ] ) ) {
				$set = true;
				update_option( $option_key, $data[ $key ] );
			}
		}

		return $set;
	}

	/**
	 * Set redirections.
	 *
	 * @param array $redirections An array of redirections to import.
	 */
	private static function set_redirections( $redirections ) {
		foreach ( $redirections as $key => $redirection ) {
			$matched = \RankMath\Redirections\DB::match_redirections_source( $redirection['sources'] );
			if ( ! empty( $matched ) || ! is_serialized( $redirection['sources'] ) ) {
				continue;
			}

			$sources = unserialize( trim( $redirection['sources'] ), [ 'allowed_classes' => false ] ); // phpcs:ignore -- We are going to move Redirections sources to JSON, that will fix this issue.
			if ( ! is_array( $sources ) || $sources instanceof \__PHP_Incomplete_Class ) {
				continue;
			}

			\RankMath\Redirections\DB::add(
				[
					'url_to'      => $redirection['url_to'],
					'sources'     => self::sanitize_sources( $sources ),
					'header_code' => $redirection['header_code'],
					'hits'        => $redirection['hits'],
					'created'     => $redirection['created'],
					'updated'     => $redirection['updated'],
				]
			);
		}
	}

	/**
	 * Sanitize the redirection source before storing it.
	 *
	 * @param array $sources An array of redirection sources.
	 */
	private static function sanitize_sources( $sources ) {
		$data = [];
		foreach ( $sources as $source ) {
			if ( empty( $source['pattern'] ) ) {
				continue;
			}

			$data[] = [
				'ignore'     => ! empty( $source['ignore'] ) ? 'case' : '',
				'pattern'    => wp_strip_all_tags( $source['pattern'], true ),
				'comparison' => in_array( $source['comparison'], [ 'exact', 'contains', 'start', 'end', 'regex' ], true ) ? $source['comparison'] : 'exact',
			];
		}

		return $data;
	}

	/**
	 * Run import hooks
	 *
	 * @param string $hook     Hook to fire.
	 * @param array  $data     Import data.
	 * @param bool   $suppress Suppress hooks or not.
	 */
	private static function run_import_hooks( $hook, $data, $suppress ) {
		if ( ! $suppress ) {
			/**
			 * Fires while importing settings.
			 *
			 * @since 0.9.0
			 *
			 * @param array $data Import data.
			 */
			do_action( 'rank_math/import/settings/' . $hook, $data );
		}
	}

	/**
	 * Gets export data.
	 *
	 * @param array $panels Which panels to export. All panels will be exported if this param is empty.
	 * @return array
	 */
	public static function get_export_data( array $panels = [] ) {
		if ( ! $panels ) {
			$panels = [ 'general', 'titles', 'sitemap', 'role-manager', 'redirections' ];
		}

		if ( ! \RankMath\Helpers\DB::check_table_exists( 'rank_math_redirections' ) ) {
			$redirections_index = array_search( 'redirections', $panels, true );
			if ( false !== $redirections_index ) {
				unset( $panels[ $redirections_index ] );
			}
		}

		$settings = rank_math()->settings->all_raw();
		$data     = [];
		foreach ( $panels as $panel ) {
			if ( isset( $settings[ $panel ] ) ) {
				$data[ $panel ] = $settings[ $panel ];
				continue;
			}

			if ( 'role-manager' === $panel ) {
				$data['role-manager'] = Helper::get_roles_capabilities();
			}

			if ( 'redirections' === $panel ) {
				$items = \RankMath\Redirections\DB::get_redirections( [ 'limit' => 1000 ] );

				$data['redirections'] = $items['redirections'];
			}

			$data = apply_filters( 'rank_math/export/settings', $data, $panel );
		}

		$data['modules'] = get_option( 'rank_math_modules', [] );

		return $data;
	}
}
