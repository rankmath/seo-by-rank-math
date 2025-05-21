<?php
/**
 * The Monitor Redirection wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Monitor_Redirection implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		return [
			'404-monitor'  => Helper::is_module_active( '404-monitor' ),
			'redirections' => Helper::is_module_active( 'redirections' ),
		];
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		Helper::update_modules(
			[
				'404-monitor'  => $values['404-monitor'] ? 'on' : 'off',
				'redirections' => $values['redirections'] ? 'on' : 'off',
			]
		);

		return true;
	}
}
