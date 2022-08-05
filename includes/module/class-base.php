<?php
/**
 * The Module Base Class
 *
 * ALl the classes inherit from this class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Module
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Module;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Base class.
 */
class Base {

	use Hooker;

	/**
	 * Screen
	 *
	 * @var string
	 */
	public static $screen = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->register_admin_page();

		if ( isset( $this->page ) && $this->page->is_current_page() ) {

			// Store the current screen ID.
			self::$screen = $this->page->parent . '_page_' . $this->page->id;

			// Register screen options.
			$this->register_screen_options();

			// Register the table if it exists.
			if ( isset( $this->table ) ) {
				$this->action( 'admin_init', 'admin_init' );
			}
		}
	}

	/**
	 * Get the current screen.
	 */
	public static function get_screen() {
		return self::$screen;
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {}

	/**
	 * Admin initialize.
	 */
	public function admin_init() {
		$this->table = new $this->table();
	}

	/**
	 * Register screen options.
	 */
	private function register_screen_options() {
		if ( ! isset( $this->screen_options ) ) {
			return;
		}

		$this->action( 'current_screen', 'add_screen_options' );
		$this->filter( 'set-screen-option', 'set_screen_options', 10, 3 );
	}

	/**
	 * Add screen options.
	 */
	public function add_screen_options() {
		add_screen_option(
			'per_page',
			[
				'option'  => $this->screen_options['id'],
				'default' => $this->screen_options['default'],
				'label'   => esc_html__( 'Items per page', 'rank-math' ),
			]
		);
	}

	/**
	 * Set screen options.
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 */
	public function set_screen_options( $status, $option, $value ) {
		return $this->screen_options['id'] === $option ? min( $value, 999 ) : $status;
	}
}
