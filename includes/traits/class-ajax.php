<?php
/**
 * The AJAX.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Traits
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Ajax class.
 */
trait Ajax {

	/**
	 * Hooks a function on to a specific ajax action
	 *
	 * @param string   $tag             The name of the action to which the $function_to_add is hooked.
	 * @param callable $function_to_add The name of the function you wish to be called.
	 * @param int      $priority        Optional. Used to specify the order in which the functions
	 *                                  associated with a particular action are executed. Default 10.
	 *                                  Lower numbers correspond with earlier execution,
	 *                                  and functions with the same priority are executed
	 *                                  in the order in which they were added to the action.
	 */
	protected function ajax( $tag, $function_to_add, $priority = 10 ) {
		\add_action( 'wp_ajax_rank_math_' . $tag, [ $this, $function_to_add ], $priority );
	}

	/**
	 * Verify request nonce
	 *
	 * @param string $action The nonce action name.
	 */
	public function verify_nonce( $action ) {
		if ( ! isset( $_REQUEST['security'] ) || ! \wp_verify_nonce( $_REQUEST['security'], $action ) ) {
			$this->error( __( 'Error: Nonce verification failed', 'rank-math' ) );
		}
	}

	/**
	 * Whether the current user has a specific capability. If not die with error.
	 *
	 * @see has_cap()
	 *
	 * @param string $capability Capability name.
	 * @return boolean Whether the current user has the given capability.
	 */
	public function has_cap_ajax( $capability ) {

		if ( ! Helper::has_cap( $capability ) ) {
			$this->error( esc_html__( 'You are not authorized to perform this action.', 'rank-math' ) );
		}

		return true;
	}

	/**
	 * Wrapper function for sending success response
	 *
	 * @param mixed $data Data to send to response.
	 */
	public function success( $data = null ) {
		$this->send( $data );
	}

	/**
	 * Wrapper function for sending error
	 *
	 * @param mixed $data Data to send to response.
	 */
	public function error( $data = null ) {
		$this->send( $data, false );
	}

	/**
	 * Send AJAX response.
	 *
	 * @param array   $data    Data to send using ajax.
	 * @param boolean $success Optional. If this is an error. Defaults: true.
	 */
	private function send( $data, $success = true ) {

		if ( is_string( $data ) ) {
			$data = $success ? [ 'message' => $data ] : [ 'error' => $data ];
		}
		$data['success'] = isset( $data['success'] ) ? $data['success'] : $success;

		\wp_send_json( $data );
	}
}
