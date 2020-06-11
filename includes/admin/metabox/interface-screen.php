<?php
/**
 * An interface for getting values for screen.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

defined( 'ABSPATH' ) || exit;

/**
 * Screen.
 */
interface IScreen {

	/**
	 * Get object id
	 *
	 * @return int
	 */
	public function get_object_id();

	/**
	 * Get object type
	 *
	 * @return string
	 */
	public function get_object_type();

	/**
	 * Get object types to register metabox to
	 *
	 * @return array
	 */
	public function get_object_types();

	/**
	 * Enqueue Styles and Scripts required for screen
	 */
	public function enqueue();

	/**
	 * Get values for localize
	 *
	 * @return array
	 */
	public function get_values();

	/**
	 * Get object values for localize
	 *
	 * @return array
	 */
	public function get_object_values();

	/**
	 * Get analysis to run.
	 *
	 * @return array
	 */
	public function get_analysis();
}
