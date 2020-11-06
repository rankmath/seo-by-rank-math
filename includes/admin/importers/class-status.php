<?php
/**
 * The Status.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Status class.
 */
class Status {

	/**
	 * The status.
	 *
	 * @var bool
	 */
	private $status = false;

	/**
	 * The message.
	 *
	 * @var string
	 */
	private $message = '';

	/**
	 * The type of action performed.
	 *
	 * @var string
	 */
	private $action;

	/**
	 * Get the status.
	 *
	 * @return bool Status.
	 */
	public function is_success() {
		return $this->status;
	}

	/**
	 * Get the message.
	 *
	 * @return string Status message.
	 */
	public function get_message() {
		if ( '' === $this->message ) {
			return $this->get_default_message();
		}

		return $this->message;
	}

	/**
	 * Get the action.
	 *
	 * @return string Action type.
	 */
	public function get_action() {
		return $this->action;
	}

	/**
	 * Set the status.
	 *
	 * @param string $status Status.
	 */
	public function set_status( $status ) {
		$this->status = $status;
	}

	/**
	 * Set the message.
	 *
	 * @param string $message Status message.
	 */
	public function set_message( $message ) {
		$this->message = $message;
	}

	/**
	 * Set the action.
	 *
	 * @param string $action Action performing.
	 */
	public function set_action( $action ) {
		$this->action = $action;
	}

	/**
	 * Get default message.
	 *
	 * @return string
	 */
	private function get_default_message() {
		$hash = [
			'settings'     => esc_html__( 'Settings imported successfully.', 'rank-math' ),
			'news'         => esc_html__( 'News Settings imported successfully.', 'rank-math' ),
			'video'        => esc_html__( 'Video Settings imported successfully.', 'rank-math' ),
			'deactivate'   => esc_html__( 'Plugin deactivated successfully.', 'rank-math' ),
			/* translators: start, end, total */
			'postmeta'     => esc_html__( 'Imported post meta for posts %1$s - %2$s out of %3$s ', 'rank-math' ),
			/* translators: total */
			'termmeta'     => esc_html__( 'Imported term meta for %s terms.', 'rank-math' ),
			/* translators: start, end, total */
			'usermeta'     => esc_html__( 'Imported user meta for users %1$s - %2$s out of %3$s ', 'rank-math' ),
			/* translators: total */
			'redirections' => esc_html__( 'Imported %s redirections.', 'rank-math' ),
			/* translators: start, end, total */
			'blocks'       => esc_html__( 'Imported blocks from posts %1$s - %2$s out of %3$s ', 'rank-math' ),
		];

		if ( false === $this->is_success() ) {
			$hash = [
				'settings'     => esc_html__( 'Settings import failed.', 'rank-math' ),
				'postmeta'     => esc_html__( 'Posts meta import failed.', 'rank-math' ),
				'termmeta'     => esc_html__( 'Term meta import failed.', 'rank-math' ),
				'usermeta'     => esc_html__( 'User meta import failed.', 'rank-math' ),
				'redirections' => esc_html__( 'There are no redirection to import.', 'rank-math' ),
				'blocks'       => esc_html__( 'Blocks import failed.', 'rank-math' ),
			];
		}

		return isset( $hash[ $this->get_action() ] ) ? $hash[ $this->get_action() ] : '';
	}
}
