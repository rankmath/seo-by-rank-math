<?php
/**
 * Interface for Rank Math ability subscribers.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Subscriber interface.
 */
interface Subscriber_Interface {

	/**
	 * Register the subscriber's category and abilities with WordPress hooks.
	 *
	 * @return void
	 */
	public function register(): void;
}
