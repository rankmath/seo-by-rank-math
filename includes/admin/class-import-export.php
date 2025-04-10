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

use WP_REST_Response;
use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Param;
use RankMath\Status\Backup;
use RankMath\Admin\Importers\Detector;

defined( 'ABSPATH' ) || exit;

/**
 * Import_Export class.
 */
class Import_Export implements Runner {

	use Hooker;
	use Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->ajax( 'clean_plugin', 'clean_plugin' );
		$this->ajax( 'import_plugin', 'import_plugin' );
	}

	/**
	 * Get localized JSON data to be used on the Import & Export tab of the Status & Tools page.
	 */
	public static function get_json_data() {
		$detector           = new Detector();
		$importable_plugins = $detector->detect();

		return [
			'backups'           => Backup::get_backups(),
			'importablePlugins' => $importable_plugins,
		];
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
}
