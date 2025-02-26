<?php
/**
 * Divi admin integration.
 *
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Divi;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Divi class.
 */
class Divi_Admin {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->init();
	}

	/**
	 * Intialize Divi admin.
	 */
	public function init() {
		$screen = get_current_screen();
		if ( 'toplevel_page_et_divi_options' === $screen->id ) {
			$this->action( 'admin_enqueue_scripts', 'enqueue_divi_admin_scripts' );
		}
	}

	/**
	 * Enqueue scripts for Divi admin options screen.
	 */
	public function enqueue_divi_admin_scripts() {
		wp_enqueue_script(
			'rank-math-divi-admin',
			rank_math()->plugin_url() . 'includes/3rdparty/divi/assets/js/divi-admin.js',
			[
				'jquery',
				'react',
				'react-dom',
				'wp-components',
				'wp-element',
				'wp-i18n',
				'wp-polyfill',
			],
			rank_math()->version,
			true
		);
	}
}
