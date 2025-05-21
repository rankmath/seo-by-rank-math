<?php
/**
 * The Ready wizard step
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
class Ready implements Wizard_Step {
	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		Helper::is_configured( true );
		return [
			'scoreImg'           => esc_url( rank_math()->plugin_url() . 'assets/admin/img/score-100.png' ),
			'dashboardUrl'       => Helper::get_dashboard_url(),
			'enable_auto_update' => boolval( Helper::get_auto_update_setting() ),
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
		$value = ! empty( $values['enable_auto_update'] ) ? 'on' : 'off';
		Helper::toggle_auto_update_setting( $value );
		return true;
	}
}
