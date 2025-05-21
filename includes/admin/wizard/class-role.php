<?php
/**
 * The Role wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;
use RankMath\Role_Manager\Capability_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Role implements Wizard_Step {
	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		Helper::is_configured( true );

		return array_merge(
			Helper::get_roles_capabilities(),
			[
				'role_manager' => Helper::is_module_active( 'role-manager' ),
				'roles'        => Helper::get_roles(),
				'capabilities' => Capability_Manager::get()->get_capabilities(),
			]
		);
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		if ( empty( $values ) ) {
			return false;
		}

		Helper::update_modules( [ 'role-manager' => $values['role_manager'] ? 'on' : 'off' ] );
		Helper::set_capabilities( $values );
		return true;
	}
}
