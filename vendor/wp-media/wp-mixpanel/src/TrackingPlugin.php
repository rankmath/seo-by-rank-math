<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

class TrackingPlugin extends Tracking {
	/**
	 * Plugin name & version
	 *
	 * @var string
	 */
	private $plugin;

	/**
	 * Brand name
	 *
	 * @var string
	 */
	private $brand;

	/**
	 * Application name
	 *
	 * @var string
	 */
	private $app;

	/**
	 * Constructor
	 *
	 * @param string $mixpanel_token Mixpanel token.
	 * @param string $plugin         Plugin name.
	 * @param string $brand          Brand name.
	 * @param string $app            Application name.
	 */
	public function __construct( string $mixpanel_token, string $plugin, string $brand = '', string $app = '' ) {
		$options = [
			'consumer'  => 'wp',
			'consumers' => [
				'wp' => 'WPMedia\\Mixpanel\\WPConsumer',
			],
		];

		parent::__construct( $mixpanel_token, $options );

		$this->plugin = $plugin;
		$this->brand  = $brand;
		$this->app    = $app;
	}

	/**
	 * Track an event in Mixpanel with plugin context
	 *
	 * @param string  $event      Event name.
	 * @param mixed[] $properties Event properties.
	 * @param string  $event_capability The capability required to track the event.
	 */
	public function track( string $event, array $properties, string $event_capability = '' ): void {
		/**
		 * Filter the default capability required to track a specific event.
		 *
		 * @param string $capability The capability required to track the event.
		 * @param string $event      The event name.
		 * @param string $app        The application name.
		 */
		$default = apply_filters( 'wp_mixpanel_event_capability', 'manage_options', $event, $this->app );

		if ( empty( $event_capability ) ) {
			$event_capability = $default;
		}

		if ( ! current_user_can( $event_capability ) ) {
			return;
		}

		$host = wp_parse_url( get_home_url(), PHP_URL_HOST );

		if ( ! $host ) {
			$host = '';
		}

		$defaults = [
			'domain'      => $this->hash( $host ),
			'wp_version'  => $this->get_wp_version(),
			'php_version' => $this->get_php_version(),
			'plugin'      => strtolower( $this->plugin ),
			'brand'       => strtolower( $this->brand ),
			'application' => strtolower( $this->app ),
		];

		$properties = array_merge( $properties, $defaults );

		parent::track( ucfirst( $event ), $properties );
	}

	/**
	 * Track opt-in status change in Mixpanel
	 *
	 * @param bool $status Opt-in status.
	 *
	 * @return void
	 */
	public function track_optin( $status ): void {
		$this->track(
			'WordPress Plugin Data Consent Changed',
			[
				'opt_in_status' => $status,
			]
		);
	}

	/**
	 * Add the Mixpanel script & initialize it
	 */
	public function add_script(): void {
		/**
		 * Filter the default capability required to track with JS.
		 *
		 * @param string $capability The capability required to track the event.
		 * @param string $app        The application name.
		 */
		$capability = apply_filters( 'wp_mixpanel_js_track_capability', 'manage_options', $this->app );

		if ( ! current_user_can( $capability ) ) {
			return;
		}

		parent::add_script();
	}
}
