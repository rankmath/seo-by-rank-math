<?php
/**
 * The wizard step contract.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

defined( 'ABSPATH' ) || exit;

/**
 * Wizard step contract.
 */
interface Wizard_Step {
	/**
	 * Localized data to be used in the step.
	 *
	 * @return array
	 */
	public static function get_localized_data();

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values );
}
