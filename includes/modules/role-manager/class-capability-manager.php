<?php
/**
 * The Capability Manager.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Role_Manager
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Role_Manager;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Capability_Manager class.
 */
class Capability_Manager {

	use Hooker;

	/**
	 * Registered capabilities.
	 *
	 * @var array
	 */
	protected $capabilities = [];

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Capability_Manager
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Capability_Manager ) ) {
			$instance = new Capability_Manager();
			$instance->set_capabilities();
		}

		return $instance;
	}

	/**
	 * Set default capabilities.
	 *
	 * @codeCoverageIgnore
	 */
	public function set_capabilities() {
		$this->register( 'rank_math_titles', esc_html__( 'Titles & Meta Settings', 'rank-math' ) );
		$this->register( 'rank_math_general', esc_html__( 'General Settings', 'rank-math' ) );
		$this->register( 'rank_math_sitemap', esc_html__( 'Sitemap Settings', 'rank-math' ) );
		$this->register( 'rank_math_404_monitor', esc_html__( '404 Monitor Log', 'rank-math' ) );
		$this->register( 'rank_math_link_builder', esc_html__( 'Link Builder', 'rank-math' ) );
		$this->register( 'rank_math_redirections', esc_html__( 'Redirections', 'rank-math' ) );
		$this->register( 'rank_math_role_manager', esc_html__( 'Role Manager', 'rank-math' ) );
		$this->register( 'rank_math_analytics', esc_html__( 'Analytics', 'rank-math' ) );
		$this->register( 'rank_math_site_analysis', esc_html__( 'Site-Wide Analysis', 'rank-math' ) );
		$this->register( 'rank_math_onpage_analysis', esc_html__( 'On-Page Analysis', 'rank-math' ) );
		$this->register( 'rank_math_onpage_general', esc_html__( 'On-Page General Settings', 'rank-math' ) );
		$this->register( 'rank_math_onpage_advanced', esc_html__( 'On-Page Advanced Settings', 'rank-math' ) );
		$this->register( 'rank_math_onpage_snippet', esc_html__( 'On-Page Schema Settings', 'rank-math' ) );
		$this->register( 'rank_math_onpage_social', esc_html__( 'On-Page Social Settings', 'rank-math' ) );
		$this->register( 'rank_math_content_ai', esc_html__( 'Content AI', 'rank-math' ) );
		$this->register( 'rank_math_admin_bar', esc_html__( 'Top Admin Bar', 'rank-math' ) );
	}

	/**
	 * Registers a capability.
	 *
	 * @param string $capability Capability to register.
	 * @param string $title      Capability human title.
	 */
	public function register( $capability, $title ) {
		$this->capabilities[ $capability ] = $title;
	}

	/**
	 * Get all registered capabilitities.
	 *
	 * @param bool $caps Capabilities as keys.
	 *
	 * @return string[] Registered capabilities.
	 */
	public function get_capabilities( $caps = false ) {
		return $caps ? array_keys( $this->capabilities ) : $this->capabilities;
	}

	/**
	 * Add capabilities on install.
	 */
	public function create_capabilities() {
		foreach ( Helper::get_roles() as $slug => $role ) {
			$role = get_role( $slug );
			if ( ! $role ) {
				continue;
			}

			$this->loop_capabilities( $this->get_default_capabilities_by_role( $slug ), 'add_cap', $role );
		}
	}

	/**
	 * Remove capabilities on uninstall.
	 */
	public function remove_capabilities() {
		$capabilities = $this->get_capabilities( true );
		foreach ( Helper::get_roles() as $slug => $role ) {
			$role = get_role( $slug );
			if ( ! $role ) {
				continue;
			}

			$this->loop_capabilities( $capabilities, 'remove_cap', $role );
		}
	}

	/**
	 * Loop capabilities and perform action.
	 *
	 * @param array  $caps    Capabilities.
	 * @param string $perform Action to perform.
	 * @param object $role    Role object.
	 */
	private function loop_capabilities( $caps, $perform, $role ) {
		foreach ( $caps as $cap ) {
			$role->$perform( $cap );
		}
	}

	/**
	 * Get default capabilities by roles.
	 *
	 * @param  string $role Capabilities for this role.
	 * @return array
	 */
	private function get_default_capabilities_by_role( $role ) {

		if ( 'administrator' === $role ) {
			return $this->get_capabilities( true );
		}

		if ( 'editor' === $role ) {
			return [
				'rank_math_site_analysis',
				'rank_math_onpage_analysis',
				'rank_math_onpage_general',
				'rank_math_onpage_snippet',
				'rank_math_onpage_social',
			];
		}

		if ( 'author' === $role ) {
			return [
				'rank_math_onpage_analysis',
				'rank_math_onpage_general',
				'rank_math_onpage_snippet',
				'rank_math_onpage_social',
			];
		}

		return [];
	}

	/**
	 * Reset capabilities.
	 *
	 * @return void
	 */
	public function reset_capabilities() {
		$this->remove_capabilities();
		$this->create_capabilities();
	}
}
