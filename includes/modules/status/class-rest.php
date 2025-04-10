<?php
/**
 * The Global functionality of the plugin.
 *
 * Defines the functionality loaded on admin.
 *
 * @since      1.0.71
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Rest class.
 */
class Rest extends WP_REST_Controller {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE . '/status';
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		register_rest_route(
			$this->namespace,
			'/getViewData',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'get_view_data' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
			]
		);
		register_rest_route(
			$this->namespace,
			'/updateViewData',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'update_view_data' ],
				'permission_callback' => [ '\\RankMath\\Rest\\Rest_Helper', 'can_manage_options' ],
			]
		);
		register_rest_route(
			$this->namespace,
			'/importSettings',
			[
				'methods'             => 'POST',
				'callback'            => [ '\\RankMath\\Status\\Import_Export_Settings', 'import' ],
				'permission_callback' => [ $this, 'has_import_export_permission' ],
			]
		);
		register_rest_route(
			$this->namespace,
			'/exportSettings',
			[
				'methods'             => 'POST',
				'callback'            => [ '\\RankMath\\Status\\Import_Export_Settings', 'export' ],
				'permission_callback' => [ $this, 'has_import_export_permission' ],
			]
		);
		register_rest_route(
			$this->namespace,
			'/runBackup',
			[
				'methods'             => 'POST',
				'callback'            => [ $this, 'run_backup' ],
				'permission_callback' => [ $this, 'has_import_export_permission' ],
			]
		);
	}

	/**
	 * Rest permission_callback method to check if user has the capability to import/export the data.
	 */
	public function has_import_export_permission() {
		if ( ! Helper::has_cap( 'general' ) ) {
			return new WP_Error(
				'rest_cannot_access',
				__( 'Sorry, you are not authorized to Import/Export the settings.', 'rank-math' ),
				[ 'status' => rest_authorization_required_code() ]
			);
		}

		return true;
	}

	/**
	 * Run Backup.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return int Credits.
	 */
	public function run_backup( WP_REST_Request $request ) {
		$action = $request->get_param( 'action' );
		if ( ! in_array( $action, [ 'create', 'delete', 'restore' ], true ) ) {
			return new WP_REST_Response(
				[
					'type'    => 'error',
					'message' => esc_html__( 'Invalid action selected.', 'rank-math' ),
				]
			);
		}

		$key    = $request->get_param( 'key' );
		$method = "{$action}_backup";
		$data   = Backup::$method( $key );
		return new WP_REST_Response(
			[
				'type'    => $data['type'],
				'message' => $data['message'],
				'backups' => isset( $data['backups'] ) ? $data['backups'] : false,
			]
		);
	}

	/**
	 * Get View data
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 *
	 * @return array View Data.
	 */
	public function get_view_data( WP_REST_Request $request ) {
		$view = $request->get_param( 'activeTab' );
		$hash = [
			'version_control' => '\RankMath\Version_Control',
			'tools'           => '\RankMath\Tools\Database_Tools',
			'status'          => '\RankMath\Status\System_Status',
			'import_export'   => '\RankMath\Admin\Import_Export',
		];
		if ( ! isset( $hash[ $view ] ) ) {
			return [];
		}

		return apply_filters(
			"rank_math/status/$view/json_data",
			$hash[ $view ]::get_json_data()
		);
	}

	/**
	 * Update Version Control data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	public function update_view_data( WP_REST_Request $request ) {
		$panel  = $request->get_param( 'panel' );
		$method = "update_{$panel}";
		return $this->$method( $request );
	}

	/**
	 * Update the Auto Update panel data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	private function update_auto_update( $request ) {
		$enable_auto_update = $request->get_param( 'autoUpdate' );
		$enable_auto_update = $enable_auto_update ? 'on' : 'off';
		Helper::toggle_auto_update_setting( $enable_auto_update );

		$enable_notifications = (bool) $request->get_param( 'updateNotificationEmail' );
		$settings             = get_option( 'rank-math-options-general', [] );

		$settings['update_notification_email'] = $enable_notifications;
		update_option( 'rank-math-options-general', $settings );

		return true;
	}

	/**
	 * Update the Auto Update panel data.
	 *
	 * @param WP_REST_Request $request Full details about the request.
	 */
	private function update_beta_optin( $request ) {
		$beta_optin = $request->get_param( 'betaOptin' );

		$settings               = get_option( 'rank-math-options-general', [] );
		$settings['beta_optin'] = $beta_optin;
		update_option( 'rank-math-options-general', $settings );

		return true;
	}
}
