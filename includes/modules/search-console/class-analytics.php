<?php
/**
 * The Search Console Analytics
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Search_Console;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Analytics class.
 */
class Analytics {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'admin_init', 'admin_init' );
		$this->action( 'current_screen', 'add_screen_options' );
		$this->filter( 'set-screen-option', 'set_screen_options', 10, 3 );
	}

	/**
	 * Admin Initialize.
	 */
	public function admin_init() {
		$this->table = new Analytics_List;
		$this->table->prepare_items();
	}

	/**
	 * Display Table.
	 */
	public function display_table() {
		echo '<form method="post" class="rank-math-sc-analytics">';
		$this->table->display();
		echo '</form>';
	}

	/**
	 * Add screen options.
	 */
	public function add_screen_options() {
		add_screen_option(
			'per_page',
			[
				'label'   => esc_html__( 'Items per page', 'rank-math' ),
				'default' => 100,
				'option'  => 'rank_math_sc_analytics_per_page',
			]
		);
	}

	/**
	 * Set screen options
	 *
	 * @param bool|int $status Screen option value. Default false to skip.
	 * @param string   $option The option name.
	 * @param int      $value  The number of rows to use.
	 */
	public function set_screen_options( $status, $option, $value ) {
		return 'rank_math_sc_analytics_per_page' === $option ? min( $value, 999 ) : $status;
	}
}
