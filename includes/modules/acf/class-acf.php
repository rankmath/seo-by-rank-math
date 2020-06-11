<?php
/**
 * The ACF Module
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\ACF
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ACF;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * ACF class.
 */
class ACF {
	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( ! Admin_Helper::is_post_edit() && ! Admin_Helper::is_term_edit() ) {
			return;
		}

		$this->action( 'rank_math/admin/enqueue_scripts', 'enqueue' );
	}

	/**
	 * Enqueue styles and scripts for the metabox.
	 */
	public function enqueue() {
		if ( Helper::is_elementor_editor() ) {
			return;
		}

		if ( ! Admin_Helper::is_post_edit() && ! Admin_Helper::is_term_edit() ) {
			return;
		}

		wp_enqueue_script( 'rank-math-acf-post-analysis', rank_math()->plugin_url() . 'assets/admin/js/acf-analysis.js', [ 'wp-hooks', 'rank-math-analyzer' ], rank_math()->version, true );

		Helper::add_json( 'acf', $this->get_config() );
	}

	/**
	 * Get Config data
	 *
	 * @return array The config data.
	 */
	private function get_config() {
		return $this->do_filter(
			'acf/config',
			[
				'pluginName'     => 'rank-math-acf',
				'headlines'      => [],
				'names'          => [],
				'refreshRate'    => $this->get_refresh_rate(),
				'blacklistTypes' => $this->get_blacklist_type(),
			]
		);
	}

	/**
	 * Retrieves the default blacklist.
	 *
	 * @return array The blacklist field types.
	 */
	private function get_blacklist_type() {
		return $this->do_filter(
			'acf/blacklist/types',
			[
				'number',
				'password',
				'file',
				'select',
				'checkbox',
				'radio',
				'true_false',
				'post_object',
				'page_link',
				'relationship',
				'user',
				'date_picker',
				'color_picker',
				'message',
				'tab',
				'repeater',
				'flexible_content',
				'group',
			]
		);
	}

	/**
	 * Get refresh rate to be used.
	 *
	 * @return int The number of milliseconds between runs.
	 */
	private function get_refresh_rate() {
		/**
		 * Refresh rate for changes to ACF fields
		 *
		 * @param int $refresh_rate Refresh rates in milliseconds
		 */
		$refresh_rate = $this->do_filter( 'acf/refresh_rate', 1000 );
		$refresh_rate = intval( $refresh_rate, 10 );

		return max( 200, $refresh_rate );
	}
}
