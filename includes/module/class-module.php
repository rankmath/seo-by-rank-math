<?php
/**
 * The Module
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\Module
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Module;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Module class.
 */
class Module {

	/**
	 * Module id.
	 *
	 * @var string
	 */
	private $id = '';

	/**
	 * Module arguments.
	 *
	 * @var string
	 */
	private $args = '';

	/**
	 * The Constructor.
	 *
	 * @param string $id   Module unique id.
	 * @param array  $args Module configuration.
	 */
	public function __construct( $id, $args ) {
		$this->id   = $id;
		$this->args = $args;
	}

	/**
	 * Getter.
	 *
	 * @param string $key     Key to get data for.
	 * @param mixed  $default Defaul value if not found.
	 *
	 * @return mixed
	 */
	public function get( $key, $default = '' ) {
		return isset( $this->args[ $key ] ) ? $this->args[ $key ] : $default;
	}

	/**
	 * Has.
	 *
	 * @param string $key Key to check data.
	 *
	 * @return bool
	 */
	public function has( $key ) {
		return isset( $this->args[ $key ] );
	}

	/**
	 * Get module id.
	 *
	 * @return string
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Get module icon.
	 *
	 * @return string
	 */
	public function get_icon() {
		return isset( $this->args['icon'] ) ? $this->args['icon'] : 'dashicons-category';
	}

	/**
	 * Echo the setting link.
	 */
	public function the_link() {
		if ( empty( $this->args['settings'] ) ) {
			return;
		}
		?>
		<a href="<?php echo esc_url( $this->args['settings'] ); ?>" class="module-settings button button-secondary"><?php esc_html_e( 'Settings', 'rank-math' ); ?></a>
		<?php
	}

	/**
	 * Is module disabled.
	 *
	 * @return bool
	 */
	public function is_disabled() {
		return isset( $this->args['disabled'] ) && $this->args['disabled'];
	}

	/**
	 * Does module has BETA version?
	 *
	 * @return bool
	 */
	public function is_betabadge() {
		return isset( $this->args['betabadge'] ) && $this->args['betabadge'];
	}

	/**
	 * Does module has PRO version?
	 *
	 * @return bool
	 */
	public function is_probadge() {
		return isset( $this->args['probadge'] ) && $this->args['probadge'];
	}

	/**
	 * Is module upgradeable?
	 *
	 * @return bool
	 */
	public function is_upgradeable() {
		return isset( $this->args['upgradeable'] ) && $this->args['upgradeable'];
	}

	/**
	 * Is PRO module.
	 *
	 * @return bool
	 */
	public function is_pro_module() {
		return isset( $this->args['probadge'] ) && $this->args['probadge'] && ! defined( 'RANK_MATH_PRO_FILE' );
	}

	/**
	 * Get the module dependencies.
	 *
	 * @return array
	 */
	public function get_dependencies() {
		return isset( $this->args['dep_modules'] ) ? $this->args['dep_modules'] : [];
	}

	/**
	 * Is module disabled.
	 *
	 * @return bool
	 */
	public function is_hidden() {
		if ( Helper::is_advanced_mode() ) {
			return false;
		}

		return in_array( $this->get_id(), [ '404-monitor', 'acf', 'bbpress', 'buddypress', 'redirections', 'role-manager', 'image-seo' ], true );
	}

	/**
	 * Is module admin only.
	 *
	 * @return bool
	 */
	public function is_admin() {
		return $this->only( 'admin' );
	}

	/**
	 * Is module internal.
	 *
	 * @return bool
	 */
	public function is_internal() {
		return $this->only( 'internal' );
	}

	/**
	 * Is module skip.
	 *
	 * @return bool
	 */
	public function is_skip() {
		return $this->only( 'skip' );
	}

	/**
	 * Is module active.
	 *
	 * @return bool
	 */
	public function is_active() {
		if ( $this->is_disabled() ) {
			return false;
		}

		$active_modules = get_option( 'rank_math_modules', [] );
		return is_array( $active_modules ) && in_array( $this->get_id(), $active_modules, true );
	}

	/**
	 * Can display module on form.
	 *
	 * @return bool
	 */
	public function can_display() {
		return ! $this->is_internal();
	}

	/**
	 * Check if we can load the module.
	 *
	 * @return bool
	 */
	public function can_load_module() {
		// If its an internal module should be loaded all the time.
		if ( $this->is_internal() ) {
			return true;
		}

		if ( ! $this->is_active() || $this->is_skip() ) {
			return false;
		}

		return true;
	}

	/**
	 * Check module only for.
	 *
	 * @param string $check Check against.
	 *
	 * @return bool
	 */
	private function only( $check ) {
		return isset( $this->args['only'] ) && $check === $this->args['only'];
	}
}
