<?php
/**
 * The Status & Tools internal module.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Module\Base;
use RankMath\Admin\Page;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Status class.
 */
class Status extends Base {

	use Hooker;

	/**
	 * Module ID.
	 *
	 * @var string
	 */
	public $id = '';

	/**
	 * Module directory.
	 *
	 * @var string
	 */
	public $directory = '';

	/**
	 * Module page.
	 *
	 * @var object
	 */
	public $page;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'rest_api_init', 'init_rest_api' );
		if ( Helper::is_heartbeat() ) {
			return;
		}

		$directory = __DIR__;
		$this->config(
			[
				'id'        => 'status',
				'directory' => $directory,
			]
		);

		parent::__construct();
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$rest = new Rest();
		$rest->register_routes();
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-status',
			esc_html__( 'Status & Tools', 'rank-math' ),
			[
				'position' => 70,
				'parent'   => 'rank-math',
				'classes'  => [ 'rank-math-page' ],
				'render'   => $this->directory . '/views/main.php',
				'assets'   => [
					'styles'  => [
						'wp-components'    => '',
						'rank-math-common' => '',
						'rank-math-status' => $uri . '/assets/css/status.css',
					],
					'scripts' => [
						'lodash'               => '',
						'rank-math-components' => '',
						'rank-math-dashboard'  => '',
						'rank-math-status'     => $uri . '/assets/js/status.js',
					],
					'json'    => $this->get_json_data( Param::get( 'view', 'version_control' ) ),
				],
			]
		);
	}

	/**
	 * Get localized JSON data based on the Page view.
	 *
	 * @param string $view Current Page view.
	 */
	private function get_json_data( $view ) {
		return [
			'isAdvancedMode'           => Helper::is_advanced_mode(),
			'isPluginActiveForNetwork' => Helper::is_plugin_active_for_network(),
			'canUser'                  => [
				'manageOptions'  => current_user_can( 'manage_options' ),
				'setupNetwork'   => current_user_can( 'setup_network' ),
				'installPlugins' => current_user_can( 'install_plugins' ),
			],
		];
	}
}
