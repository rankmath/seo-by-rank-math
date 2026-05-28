<?php
/**
 * Interface for Rank Math abilities.
 *
 * @since      1.0.271
 * @package    RankMath
 * @subpackage RankMath\Abilities
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Abilities;

defined( 'ABSPATH' ) || exit;

/**
 * Ability interface.
 */
interface Ability_Interface {

	/**
	 * Register the ability with the WordPress Abilities API.
	 *
	 * @return void
	 */
	public function register(): void;

	/**
	 * Check if the current user has permission to execute this ability.
	 *
	 * @return bool
	 */
	public function check_permissions(): bool;

	/**
	 * Execute the ability.
	 *
	 * @param array $input Ability input arguments.
	 * @return array
	 */
	public function execute( array $input = [] ): array;
}
