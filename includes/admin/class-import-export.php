<?php
/**
 * The Import Export Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;
use RankMath\Admin\Importers\Detector;

defined( 'ABSPATH' ) || exit;

/**
 * Import_Export class.
 */
class Import_Export implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_init', 'handler' );
		$this->action( 'admin_enqueue_scripts', 'enqueue', 1 );
		$this->filter( 'rank_math/tools/pages', 'add_status_page', 30 );
		$this->filter( 'rank_math/export/settings', 'export_other_panels', 10, 2 );
		$this->action( 'rank_math/import/settings/pre_import', 'run_backup', 10, 0 );

		$this->ajax( 'create_backup', 'create_backup' );
		$this->ajax( 'delete_backup', 'delete_backup' );
		$this->ajax( 'restore_backup', 'restore_backup' );
		$this->ajax( 'clean_plugin', 'clean_plugin' );
		$this->ajax( 'import_plugin', 'import_plugin' );
	}

	/**
	 * Add subpage to Status & Tools screen.
	 *
	 * @param array $pages Pages.
	 * @return array       New pages.
	 */
	public function add_status_page( $pages ) {
		if ( Helper::is_advanced_mode() ) {
			$pages['import_export'] = [
				'url'   => 'status',
				'args'  => 'view=import_export',
				'cap'   => 'manage_options',
				'title' => __( 'Import & Export', 'rank-math' ),
				'class' => '\\RankMath\\Admin\\Import_Export',
			];
		}

		return $pages;
	}

	/**
	 * Display Import/Export tools page.
	 *
	 * @return void
	 */
	public function display() {
		include $this->get_view( 'main' );
	}

	/**
	 * Display panels for Import/Export tools.
	 *
	 * @return void
	 */
	public function show_panels() {
		foreach ( (array) $this->get_panels() as $panel ) {
			if ( ! isset( $panel['view'] ) || ! file_exists( $panel['view'] ) ) {
				continue;
			}

			echo '<div class="' . ( isset( $panel['class'] ) ? esc_attr( $panel['class'] ) : '' ) . '">';
			include $panel['view'];
			echo '</div>';
		}
	}

	/**
	 * Get list of panels.
	 *
	 * @return array
	 */
	public function get_panels() {
		$dir = dirname( __FILE__ ) . '/views/import-export/';

		$panels = [
			'import-export' => [
				'view'  => $dir . 'import-export-panel.php',
				'class' => 'import-export-settings',
			],
			'plugins'       => [
				'view'  => $dir . 'plugins-panel.php',
				'class' => 'import-plugins',
			],
			'backup'        => [
				'view'  => $dir . 'backup-panel.php',
				'class' => 'settings-backup',
			],
		];

		return apply_filters( 'rank_math/admin/import_export_panels', $panels );
	}

	/**
	 * Get view file.
	 *
	 * @param string $view View filename.
	 *
	 * @return string Complete path to view
	 */
	public function get_view( $view ) {
		$view = sanitize_key( $view );
		return rank_math()->admin_dir() . "views/import-export/{$view}.php";
	}

	/**
	 * Enqueue files & add JSON.
	 *
	 * @return void
	 */
	public function enqueue() {
		if ( ! $this->is_import_export_page() ) {
			return;
		}

		\RankMath\Tools\Update_Score::get()->enqueue();

		wp_enqueue_script( 'rank-math-import-export', rank_math()->plugin_url() . 'assets/admin/js/import-export.js', [], rank_math()->version, true );
		wp_enqueue_style( 'cmb2-styles' );
		wp_enqueue_style( 'rank-math-common' );
		wp_enqueue_style( 'rank-math-cmb2' );

		Helper::add_json( 'importSettingsConfirm', esc_html__( 'Are you sure you want to import settings into Rank Math? Don\'t worry, your current configuration will be saved as a backup.', 'rank-math' ) );

		// Translators: %s is the plugin name.
		Helper::add_json( 'importPluginConfirm', esc_html__( 'Are you sure you want to import data from %s?', 'rank-math' ) );
		Helper::add_json( 'importPluginSelectAction', esc_html__( 'Select data to import.', 'rank-math' ) );
		Helper::add_json( 'restoreConfirm', esc_html__( 'Are you sure you want to restore this backup? Your current configuration will be overwritten.', 'rank-math' ) );
		Helper::add_json( 'deleteBackupConfirm', esc_html__( 'Are you sure you want to delete this backup?', 'rank-math' ) );

		// Translators: %s is the plugin name.
		Helper::add_json( 'cleanPluginConfirm', esc_html__( 'Are you sure you want erase all traces of %s?', 'rank-math' ) );
	}

	/**
	 * Check if we're on the Tools > Import & Export admin page.
	 *
	 * @return boolean
	 */
	private function is_import_export_page() {
		return Param::get( 'page' ) === 'rank-math-status' && Param::get( 'view' ) === 'import_export';
	}

	/**
	 * Handle import or export.
	 */
	public function handler() {
		$object_id = Param::post( 'object_id' );
		if ( false === $object_id ) {
			return;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return false;
		}

		if ( 'export-plz' === $object_id && check_admin_referer( 'rank-math-export-settings' ) ) {
			$this->export();
		}

		if ( isset( $_FILES['import-me'] ) && 'import-plz' === $object_id && check_admin_referer( 'rank-math-import-settings' ) ) {
			$this->import();
		}
	}

	/**
	 * Handles AJAX run plugin clean.
	 */
	public function clean_plugin() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$result = Detector::run_by_slug( Param::post( 'pluginSlug' ), 'cleanup' );

		if ( $result['status'] ) {
			/* translators: Plugin name */
			$this->success( sprintf( esc_html__( 'Cleanup of %s data successfully done.', 'rank-math' ), $result['importer']->get_plugin_name() ) );
		}

		/* translators: Plugin name */
		$this->error( sprintf( esc_html__( 'Cleanup of %s data failed.', 'rank-math' ), $result['importer']->get_plugin_name() ) );
	}

	/**
	 * Handles AJAX run plugin import.
	 */
	public function import_plugin() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$perform = Param::post( 'perform', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		if ( ! $this->is_action_allowed( $perform ) ) {
			$this->error( esc_html__( 'Action not allowed.', 'rank-math' ) );
		}

		try {
			$result = Detector::run_by_slug( Param::post( 'pluginSlug' ), 'import', $perform );
			$this->success( $result );
		} catch ( \Exception $e ) {
			$this->error( $e->getMessage() );
		}
	}

	/**
	 * Handles AJAX create backup.
	 */
	public function create_backup() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$key = $this->run_backup();
		if ( is_null( $key ) ) {
			$this->error( esc_html__( 'Unable to create backup this time.', 'rank-math' ) );
		}

		$this->success(
			[
				'key'     => $key,
				/* translators: Backup formatted date */
				'backup'  => sprintf( esc_html__( 'Backup: %s', 'rank-math' ), date_i18n( 'M jS Y, H:i a', $key ) ),
				'message' => esc_html__( 'Backup created successfully.', 'rank-math' ),
			]
		);
	}

	/**
	 * Handles AJAX delete backup.
	 */
	public function delete_backup() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$key = Param::post( 'key', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		if ( ! $key ) {
			$this->error( esc_html__( 'No backup key found to delete.', 'rank-math' ) );
		}

		$this->run_backup( 'delete', $key );
		$this->success( esc_html__( 'Backup successfully deleted.', 'rank-math' ) );
	}

	/**
	 * Handles AJAX restore backup.
	 */
	public function restore_backup() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$key = Param::post( 'key', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		if ( ! $key ) {
			$this->error( esc_html__( 'No backup key found to restore.', 'rank-math' ) );
		}

		if ( ! $this->run_backup( 'restore', $key ) ) {
			$this->error( esc_html__( 'Backup does not exist.', 'rank-math' ) );
		}

		$this->success( esc_html__( 'Backup restored successfully.', 'rank-math' ) );
	}

	/**
	 * Run backup actions.
	 *
	 * @param  string $action Action to perform.
	 * @param  array  $key    Key to backup.
	 * @return mixed
	 */
	public function run_backup( $action = 'add', $key = null ) {
		$backups = $this->get_backups();

		// Restore.
		if ( 'restore' === $action ) {
			if ( ! isset( $backups[ $key ] ) ) {
				return false;
			}

			$this->do_import_data( $backups[ $key ], true );

			return true;
		}

		// Add.
		if ( 'add' === $action ) {
			$key     = current_time( 'U' );
			$backups = [ $key => $this->get_export_data() ] + $backups;
		}

		// Delete.
		if ( 'delete' === $action && isset( $backups[ $key ] ) ) {
			unset( $backups[ $key ] );
		}

		update_option( 'rank_math_backups', $backups, false );

		return $key;
	}

	/**
	 * Handle export.
	 */
	private function export() {
		$panels   = Param::post( 'panels', [], FILTER_DEFAULT, FILTER_REQUIRE_ARRAY );
		$data     = $this->get_export_data( $panels );
		$filename = 'rank-math-settings-' . date_i18n( 'Y-m-d-H-i-s' ) . '.json';

		header( 'Content-Type: application/json' );
		header( 'Content-Disposition: attachment; filename=' . $filename );
		header( 'Cache-Control: no-cache, no-store, must-revalidate' );
		header( 'Pragma: no-cache' );
		header( 'Expires: 0' );

		echo wp_json_encode( $data );
		exit;
	}

	/**
	 * Handle import.
	 */
	private function import() {
		$file = $this->has_valid_file();
		if ( false === $file ) {
			return false;
		}

		// Parse Options.
		$wp_filesystem = Helper::get_filesystem();
		if ( is_null( $wp_filesystem ) || ! Helper::is_filesystem_direct() ) {
			Helper::add_notification( esc_html__( 'Uploaded file could not be read.', 'rank-math' ), [ 'type' => 'error' ] );
			return false;
		}

		$settings = $wp_filesystem->get_contents( $file['file'] );
		$settings = json_decode( $settings, true );

		\unlink( $file['file'] );

		if ( is_array( $settings ) && $this->do_import_data( $settings ) ) {
			Helper::add_notification( esc_html__( 'Settings successfully imported. Your old configuration has been saved as a backup.', 'rank-math' ), [ 'type' => 'success' ] );
			return;
		}

		Helper::add_notification( esc_html__( 'No settings found to be imported.', 'rank-math' ), [ 'type' => 'info' ] );
	}

	/**
	 * Import has valid file.
	 *
	 * @return mixed
	 */
	private function has_valid_file() {
		// Add upload hooks.
		$this->filter( 'upload_mimes', 'allow_txt_upload', 10, 2 );
		$this->filter( 'wp_check_filetype_and_ext', 'filetype_and_ext', 10, 4 );

		// Do the upload.
		$file = wp_handle_upload( $_FILES['import-me'], [ 'test_form' => false ] );

		// Remove upload hooks.
		$this->remove_filter( 'upload_mimes', 'allow_txt_upload', 10 );
		$this->remove_filter( 'wp_check_filetype_and_ext', 'filetype_and_ext', 10 );

		if ( is_wp_error( $file ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported:', 'rank-math' ) . ' ' . $file->get_error_message(), [ 'type' => 'error' ] );
			return false;
		}

		if ( isset( $file['error'] ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported:', 'rank-math' ) . ' ' . $file['error'], [ 'type' => 'error' ] );
			return false;
		}

		if ( ! isset( $file['file'] ) ) {
			Helper::add_notification( esc_html__( 'Settings could not be imported: Upload failed.', 'rank-math' ), [ 'type' => 'error' ] );
			return false;
		}

		return $file;
	}

	/**
	 * Filters the "real" file type of the given file.
	 *
	 * @param array    $types {
	 *     Values for the extension, mime type, and corrected filename.
	 *
	 *     @type string|false $ext             File extension, or false if the file doesn't match a mime type.
	 *     @type string|false $type            File mime type, or false if the file doesn't match a mime type.
	 *     @type string|false $proper_filename File name with its correct extension, or false if it cannot be determined.
	 * }
	 * @param string   $file                      Full path to the file.
	 * @param string   $filename                  The name of the file (may differ from $file due to
	 *                                                $file being in a tmp directory).
	 * @param string[] $mimes                     Array of mime types keyed by their file extension regex.
	 *
	 * @return array
	 */
	public function filetype_and_ext( $types, $file, $filename, $mimes ) {
		if ( false !== strpos( $filename, '.json' ) ) {
			$types['ext']  = 'json';
			$types['type'] = 'application/json';
		} elseif ( false !== strpos( $filename, '.txt' ) ) {
			$types['ext']  = 'txt';
			$types['type'] = 'text/plain';
		}

		return $types;
	}

	/**
	 * Allow txt & json file upload.
	 *
	 * @param array            $types    Mime types keyed by the file extension regex corresponding to those types.
	 * @param int|WP_User|null $user User ID, User object or null if not provided (indicates current user).
	 *
	 * @return array
	 */
	public function allow_txt_upload( $types, $user ) {
		$types['json'] = 'application/json';
		$types['txt']  = 'text/plain';

		return $types;
	}

	/**
	 * Does import data.
	 *
	 * @param  array $data           Import data.
	 * @param  bool  $suppress_hooks Suppress hooks or not.
	 * @return bool
	 */
	private function do_import_data( array $data, $suppress_hooks = false ) {
		$this->run_import_hooks( 'pre_import', $data, $suppress_hooks );

		// Import options.
		$down = $this->set_options( $data );

		// Import capabilities.
		if ( ! empty( $data['role-manager'] ) ) {
			$down = true;
			Helper::set_capabilities( $data['role-manager'] );
		}

		// Import redirections.
		if ( ! empty( $data['redirections'] ) ) {
			$down = true;
			$this->set_redirections( $data['redirections'] );
		}

		$this->run_import_hooks( 'after_import', $data, $suppress_hooks );

		return $down;
	}

	/**
	 * Set options from data.
	 *
	 * @param array $data An array of data.
	 */
	private function set_options( $data ) {
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
	private function set_redirections( $redirections ) {
		foreach ( $redirections as $key => $redirection ) {
			$matched = \RankMath\Redirections\DB::match_redirections_source( $redirection['sources'] );
			if ( ! empty( $matched ) || ! is_serialized( $redirection['sources'] ) ) {
				continue;
			}

			$sources = unserialize( trim( $redirection['sources'] ), [ 'allowed_classes' => false ] );
			if ( ! is_array( $sources ) || $sources instanceof \__PHP_Incomplete_Class ) {
				continue;
			}

			\RankMath\Redirections\DB::add(
				[
					'url_to'      => $redirection['url_to'],
					'sources'     => $this->sanitize_sources( $sources ),
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
	private function sanitize_sources( $sources ) {
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
	private function run_import_hooks( $hook, $data, $suppress ) {
		if ( ! $suppress ) {
			/**
			 * Fires while importing settings.
			 *
			 * @since 0.9.0
			 *
			 * @param array $data Import data.
			 */
			$this->do_action( 'import/settings/' . $hook, $data );
		}
	}

	/**
	 * Gets export data.
	 *
	 * @param array $panels Which panels to export. All panels will be exported if this param is empty.
	 * @return array
	 */
	private function get_export_data( array $panels = [] ) {
		if ( ! $panels ) {
			$panels = [ 'general', 'titles', 'sitemap', 'role-manager', 'redirections' ];
		}

		$settings = rank_math()->settings->all_raw();
		$data     = [];
		foreach ( $panels as $panel ) {
			if ( isset( $settings[ $panel ] ) ) {
				$data[ $panel ] = $settings[ $panel ];
				continue;
			}

			$data = $this->do_filter( 'export/settings', $data, $panel );
		}

		$data['modules'] = get_option( 'rank_math_modules', [] );

		return $data;
	}

	/**
	 * Export other panels.
	 *
	 * @param array  $data  Gathered data.
	 * @param string $panel Panel id.
	 *
	 * @return array
	 */
	public function export_other_panels( $data, $panel ) {
		if ( 'role-manager' === $panel ) {
			$data['role-manager'] = Helper::get_roles_capabilities();
		}

		if ( 'redirections' === $panel ) {
			$items = \RankMath\Redirections\DB::get_redirections( [ 'limit' => 1000 ] );

			$data['redirections'] = $items['redirections'];
		}

		return $data;
	}

	/**
	 * Check if given action is in the list of allowed actions.
	 *
	 * @param string $perform Action to check.
	 *
	 * @return bool
	 */
	private function is_action_allowed( $perform ) {
		$allowed = [ 'settings', 'postmeta', 'termmeta', 'usermeta', 'redirections', 'blocks', 'deactivate', 'locations', 'news', 'video', 'recalculate' ];
		return $perform && in_array( $perform, $allowed, true );
	}

	/**
	 * Get backups from the database.
	 */
	public function get_backups() {
		$backups = get_option( 'rank_math_backups', [] );
		if ( empty( $backups ) ) {
			$backups = [];
		} elseif ( ! is_array( $backups ) ) {
			$backups = (array) $backups;
		}

		return $backups;
	}
}
