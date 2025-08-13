<?php
/**
 * The option page functionality of the plugin.
 *
 * @since      1.0.250
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Register_Options_Page class.
 */
class Register_Options_Page {

	/**
	 * The Constructor
	 *
	 * @param array $config Array of configuration.
	 */
	public function __construct( $config ) {
		if ( ! Helper::is_react_enabled() ) {
			new CMB2_Options( $config );
			return;
		}

		$options_page = new Options( $config );
		$options_page->register_option_page();
	}
}
