<?php
/**
 * The Role Manager Module.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Role_Manager
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Role_Manager;

use RankMath\Helper;
use RankMath\Helpers\Param;
use RankMath\Module\Base;
use RankMath\Admin\Page;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Role_Manager class.
 */
class Role_Manager extends Base {
	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		$directory = dirname( __FILE__ );
		$this->config(
			[
				'id'        => 'role-manager',
				'directory' => $directory,
			]
		);
		parent::__construct();

		// Members plugin integration.
		if ( \function_exists( 'members_plugin' ) ) {
			new Members();
		}

		// User Role Editor plugin integration.
		if ( defined( 'URE_PLUGIN_URL' ) ) {
			new User_Role_Editor();
		}
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );

		$this->page = new Page(
			'rank-math-role-manager',
			esc_html__( 'Role Manager', 'rank-math' ),
			[
				'position'   => 20,
				'parent'     => 'rank-math',
				'capability' => 'rank_math_role_manager',
				'classes'    => [ 'rank-math-page' ],
				'render'     => 'settings',
				'assets'     => [
					'styles'  => [
						'rank-math-common' => '',
						'wp-components'    => '',
					],
					'scripts' => [
						'lodash'                 => '',
						'wp-element'             => '',
						'wp-data'                => '',
						'wp-components'          => '',
						'wp-api-fetch'           => '',
						'rank-math-components'   => '',
						'rank-math-role-manager' => $uri . '/assets/js/role-manager.js',
					],
					'json'    => [
						'roles'            => Helper::get_roles(),
						'roleCapabilities' => Helper::get_roles_capabilities(),
						'capabilities'     => Capability_Manager::get()->get_capabilities(),
					],
				],
			]
		);
	}
}
