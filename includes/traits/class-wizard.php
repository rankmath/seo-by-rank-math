<?php
/**
 * The Wizard pages helper.
 *
 * @since      1.0.3
 * @package    RankMath
 * @subpackage RankMath\Traits
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

use RankMath\Helper as GlobalHelper;
use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Wizard class.
 */
trait Wizard {

	/**
	 * Output the content for the current step.
	 */
	public function body() {
		if ( ! isset( $this->steps[ $this->step ] ) ) {
			return;
		}

		if ( ! is_null( $this->wizard_step ) ) {
			$this->wizard_step->render( $this );
			return;
		}

		if ( is_callable( $this->steps[ $this->step ]['view'] ) ) {
			call_user_func( $this->steps[ $this->step ]['view'], $this );
			return;
		}

		include_once $this->steps[ $this->step ]['view'];
	}

	/**
	 * Get the next step link.
	 */
	public function step_next_link() {
		$keys = array_keys( $this->steps );
		$step = array_search( $this->step, $keys, true ) + 1;

		return Security::add_query_arg_raw(
			'step',
			isset( $keys[ $step ] ) ? $keys[ $step ] : '',
			GlobalHelper::get_admin_url( 'wizard' )
		);
	}

	/**
	 * Is the page is currrent page.
	 *
	 * @return boolean
	 */
	public function is_current_page() {
		$page = isset( $_GET['page'] ) && ! empty( $_GET['page'] ) ? filter_input( INPUT_GET, 'page' ) : false;
		return $page === $this->slug;
	}
}
