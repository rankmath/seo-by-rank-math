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
use RankMath\Module\Base;
use MyThemeShop\Admin\Page;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Role_Manager class.
 */
class Role_Manager extends Base {

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

		$this->action( 'cmb2_admin_init', 'register_form' );
		add_filter( 'cmb2_override_option_get_rank-math-role-manager', [ '\RankMath\Helper', 'get_roles_capabilities' ] );
		$this->action( 'admin_post_rank_math_save_capabilities', 'save_capabilities' );

		if ( $this->page->is_current_page() ) {
			add_action( 'admin_enqueue_scripts', [ 'CMB2_Hookup', 'enqueue_cmb_css' ], 25 );
		}

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
				'render'     => $this->directory . '/views/main.php',
				'classes'    => [ 'rank-math-page' ],
				'assets'     => [
					'styles' => [
						'rank-math-common'       => '',
						'rank-math-cmb2'         => '',
						'rank-math-role-manager' => $uri . '/assets/css/role-manager.css',
					],
				],
			]
		);
	}

	/**
	 * Register form for Add New Record.
	 */
	public function register_form() {

		$cmb = new_cmb2_box(
			[
				'id'           => 'rank-math-role-manager',
				'object_types' => [ 'options-page' ],
				'option_key'   => 'rank-math-role-manager',
				'hookup'       => false,
				'save_fields'  => false,
			]
		);

		$caps = Capability_Manager::get()->get_capabilities();

		foreach ( WordPress::get_roles() as $role => $label ) {
			$cmb->add_field(
				[
					'id'                => esc_attr( $role ),
					'type'              => 'multicheck_inline',
					'name'              => translate_user_role( $label ),
					'options'           => $caps,
					'select_all_button' => false,
					'classes'           => 'cmb-big-labels',
				]
			);
		}
	}

	/**
	 * Save capabilities form submit handler.
	 */
	public function save_capabilities() {

		// If no form submission, bail!
		if ( empty( $_POST ) ) {
			return false;
		}

		check_admin_referer( 'rank-math-save-capabilities', 'security' );

		if ( ! Helper::has_cap( 'role_manager' ) ) {
			Helper::add_notification( esc_html__( 'You are not authorized to perform this action.', 'rank-math' ), [ 'type' => 'error' ] );
			wp_safe_redirect( Helper::get_admin_url( 'role-manager' ) );
			exit;
		}

		$cmb = cmb2_get_metabox( 'rank-math-role-manager' );
		Helper::set_capabilities( $cmb->get_sanitized_values( $_POST ) );

		wp_safe_redirect( Helper::get_admin_url( 'role-manager' ) );
		exit;
	}
}
