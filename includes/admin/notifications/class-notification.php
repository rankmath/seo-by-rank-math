<?php
/**
 * The Notification
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin\Notifications;

use RankMath\Helpers\Str;
use RankMath\Helpers\HTML;

/**
 * Notification class.
 */
class Notification {

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const ERROR = 'error';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const SUCCESS = 'success';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const INFO = 'info';

	/**
	 * Notification type.
	 *
	 * @var string
	 */
	const WARNING = 'warning';

	/**
	 * Screen check.
	 *
	 * @var string
	 */
	const SCREEN_ANY = 'any';

	/**
	 * User capability check.
	 *
	 * @var string
	 */
	const CAPABILITY_ANY = '';

	/**
	 * The notification message.
	 *
	 * @var string
	 */
	public $message = '';

	/**
	 * Contains optional arguments:
	 *
	 * -             type: The notification type, i.e. 'updated' or 'error'
	 * -               id: The ID of the notification
	 * -    dismissal_key: Option name to save dismissal information in, ID will be used if not supplied.
	 * -           screen: Only display on plugin page or on every page.
	 *
	 * @var array Options of this Notification.
	 */
	public $options = [];

	/**
	 * Internal flag for whether notifications has been displayed.
	 *
	 * @var bool
	 */
	private $displayed = false;

	/**
	 * Notification class constructor.
	 *
	 * @param string $message Message string.
	 * @param array  $options Set of options.
	 */
	public function __construct( $message, $options = [] ) {
		$this->message = $message;
		$this->options = wp_parse_args(
			$options,
			[
				'id'         => '',
				'classes'    => '',
				'type'       => self::SUCCESS,
				'screen'     => self::SCREEN_ANY,
				'capability' => self::CAPABILITY_ANY,
			]
		);
	}

	/**
	 * Adds string (view) behaviour to the Notification.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string
	 */
	public function __toString() {
		return $this->render();
	}

	/**
	 * Return data from options.
	 *
	 * @param  string $id Data to get.
	 * @return mixed
	 */
	public function args( $id ) {
		return $this->options[ $id ];
	}

	/**
	 * Is this Notification persistent.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool True if persistent, False if fire and forget.
	 */
	public function is_persistent() {
		return ! empty( $this->args( 'id' ) );
	}

	/**
	 * Is this notification displayed.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool
	 */
	public function is_displayed() {
		return $this->displayed;
	}

	/**
	 * Can display on current screen.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return bool
	 */
	public function can_display() {
		// Removed.
		if ( $this->displayed ) {
			return false;
		}

		$screen = get_current_screen();
		if ( self::SCREEN_ANY === $this->args( 'screen' ) || Str::contains( $this->args( 'screen' ), $screen->id ) ) {
			$this->displayed = true;
		}

		if ( self::CAPABILITY_ANY !== $this->args( 'capability' ) && ! current_user_can( $this->args( 'capability' ) ) ) {
			$this->displayed = false;
		}

		return $this->displayed;
	}

	/**
	 * Dismiss persisten notification.
	 *
	 * @codeCoverageIgnore
	 */
	public function dismiss() {
		$this->displayed     = true;
		$this->options['id'] = '';
	}

	/**
	 * Return the object properties as an array.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return array
	 */
	public function to_array() {
		return [
			'message' => $this->message,
			'options' => $this->options,
		];
	}

	/**
	 * Renders the notification as a string.
	 *
	 * @codeCoverageIgnore
	 *
	 * @return string The rendered notification.
	 */
	public function render() {
		$attributes = [];

		// Default notification classes.
		$classes = [
			'notice',
			'notice-' . $this->args( 'type' ),
			$this->args( 'classes' ),
		];

		// Maintain WordPress visualisation of alerts when they are not persistent.
		if ( $this->is_persistent() ) {
			$classes[]                   = 'is-dismissible';
			$classes[]                   = 'wp-helpers-notice';
			$attributes['id']            = $this->args( 'id' );
			$attributes['data-security'] = wp_create_nonce( $this->args( 'id' ) );
		}

		if ( ! empty( $classes ) ) {
			$attributes['class'] = implode( ' ', array_filter( $classes ) );
		}

		// Build the output DIV.
		$output = '<div' . HTML::attributes_to_string( $attributes ) . '>' . wpautop( $this->message ) . '</div>' . PHP_EOL;

		/**
		 * Filter: 'wp_helpers_notifications_render' - Allows developer to filter notifications before the output is finalized.
		 *
		 * @param string $output  HTML output.
		 * @param string $message Notice message.
		 * @param array  $options Notice args.
		 */
		$output = apply_filters( 'wp_helpers_notifications_render', $output, $this->message, $this->options );

		return $output;
	}
}
