<?php
declare(strict_types=1);

namespace WPMedia\Mixpanel;

use WPMedia_Mixpanel;

class Tracking {
	const HOST = 'mixpanel-tracking-proxy-prod.public-default.live2-k8s-cph3.ingress.k8s.g1i.one';

	/**
	 * Mixpanel instance
	 *
	 * @var WPMedia_Mixpanel
	 */
	private $mixpanel;

	/**
	 * Mixpanel token
	 *
	 * @var string
	 */
	protected $token;

	/**
	 * Constructor
	 *
	 * @param string  $mixpanel_token Mixpanel token.
	 * @param mixed[] $options Options for Mixpanel instance.
	 */
	public function __construct( string $mixpanel_token, array $options = [] ) {
		$mixpanel_options = array_merge(
			[
				'host'            => self::HOST,
				'events_endpoint' => '/track/?ip=0',
			],
			$options
		);

		$this->token    = $mixpanel_token;
		$this->mixpanel = WPMedia_Mixpanel::getInstance(
			$this->token,
			$mixpanel_options
		);
	}

	/**
	 * Check if debug mode is enabled
	 *
	 * @return bool
	 */
	private function is_debug(): bool {
		$debug = ( defined( 'WP_DEBUG' ) ? constant( 'WP_DEBUG' ) : false )
			&& ( defined( 'WP_DEBUG_LOG' ) ? constant( 'WP_DEBUG_LOG' ) : false );

		/**
		 * Filters whether Mixpanel debug mode is enabled.
		 *
		 * @param bool $debug Debug mode value.
		 */
		return apply_filters( 'wp_media_mixpanel_debug', $debug );
	}

	/**
	 * Log event to error log if debug mode is enabled
	 *
	 * @param string  $event Event name.
	 * @param mixed[] $properties Event properties.
	 */
	private function log_event( string $event, array $properties ): void {
		if ( ! $this->is_debug() ) {
			return;
		}

		error_log( 'Mixpanel event: ' . $event . ' ' . var_export( $properties, true ) ); // phpcs:ignore WordPress.PHP.DevelopmentFunctions.error_log_error_log, WordPress.PHP.DevelopmentFunctions.error_log_var_export
	}

	/**
	 * Track an event in Mixpanel
	 *
	 * @param string  $event Event name.
	 * @param mixed[] $properties Event properties.
	 */
	public function track( string $event, array $properties ): void {
		$this->log_event( $event, $properties );

		$this->mixpanel->track( $event, $properties );
	}

	/**
	 * Identify a user in Mixpanel
	 *
	 * @param string $user_id User ID.
	 *
	 * @return void
	 */
	public function identify( string $user_id ): void {
		$this->mixpanel->identify( $this->hash( $user_id ) );
	}

	/**
	 * Set a user property in Mixpanel
	 *
	 * @param string $user_id User ID.
	 * @param string $property Property name.
	 * @param mixed  $value Property value.
	 */
	public function set_user_property( string $user_id, string $property, $value ): void {
		$this->mixpanel->people->set(
			$user_id,
			[
				$property => $value,
			],
			'0'
		);
	}

	/**
	 * Hash a value using sha224
	 *
	 * @param string $value Value to hash.
	 *
	 * @return string
	 */
	public function hash( string $value ): string {
		return hash( 'sha224', $value );
	}

	/**
	 * Get the WordPress version
	 *
	 * @return string
	 */
	public function get_wp_version(): string {
		$version = preg_replace( '@^(\d\.\d+).*@', '\1', get_bloginfo( 'version' ) );

		if ( null === $version ) {
			$version = '0.0';
		}

		return $version;
	}

	/**
	 * Get the PHP version
	 *
	 * @return string
	 */
	public function get_php_version(): string {
		$version = preg_replace( '@^(\d\.\d+).*@', '\1', phpversion() );

		if ( null === $version ) {
			$version = '0.0';
		}

		return $version;
	}

	/**
	 * Get the active theme
	 *
	 * @return string
	 */
	public function get_current_theme(): string {
		$theme = wp_get_theme();

		return $theme->get( 'Name' );
	}

	/**
	 * Get list of active plugins names
	 *
	 * @return string[]
	 */
	public function get_active_plugins(): array {
		$plugins        = [];
		$active_plugins = (array) get_option( 'active_plugins', [] );
		$all_plugins    = get_plugins();

		foreach ( $active_plugins as $plugin_path ) {
			if ( ! is_string( $plugin_path ) ) {
				continue;
			}

			if ( ! isset( $all_plugins[ $plugin_path ] ) ) {
				continue;
			}

			$plugins[] = $all_plugins[ $plugin_path ]['Name'];
		}

		return $plugins;
	}

	/**
	 * Get the Mixpanel token
	 *
	 * @return string
	 */
	public function get_token(): string {
		return $this->token;
	}

	/**
	 * Add the Mixpanel script & initialize it
	 */
	public function add_script(): void {
		?>
		<!-- start Mixpanel --><script>
		const MIXPANEL_CUSTOM_LIB_URL = "https://<?php echo esc_js( self::HOST ); ?>/lib.min.js";
		(function (f, b) { if (!b.__SV) { var e, g, i, h; window.mixpanel = b; b._i = []; b.init = function (e, f, c) { function g(a, d) { var b = d.split("."); 2 == b.length && ((a = a[b[0]]), (d = b[1])); a[d] = function () { a.push([d].concat(Array.prototype.slice.call(arguments, 0))); }; } var a = b; "undefined" !== typeof c ? (a = b[c] = []) : (c = "mixpanel"); a.people = a.people || []; a.toString = function (a) { var d = "mixpanel"; "mixpanel" !== c && (d += "." + c); a || (d += " (stub)"); return d; }; a.people.toString = function () { return a.toString(1) + ".people (stub)"; }; i = "disable time_event track track_pageview track_links track_forms track_with_groups add_group set_group remove_group register register_once alias unregister identify name_tag set_config reset opt_in_tracking opt_out_tracking has_opted_in_tracking has_opted_out_tracking clear_opt_in_out_tracking start_batch_senders people.set people.set_once people.unset people.increment people.append people.union people.track_charge people.clear_charges people.delete_user people.remove".split( " "); for (h = 0; h < i.length; h++) g(a, i[h]); var j = "set set_once union unset remove delete".split(" "); a.get_group = function () { function b(c) { d[c] = function () { call2_args = arguments; call2 = [c].concat(Array.prototype.slice.call(call2_args, 0)); a.push([e, call2]); }; } for ( var d = {}, e = ["get_group"].concat( Array.prototype.slice.call(arguments, 0)), c = 0; c < j.length; c++) b(j[c]); return d; }; b._i.push([e, f, c]); }; b.__SV = 1.2; e = f.createElement("script"); e.type = "text/javascript"; e.async = !0; e.src = "undefined" !== typeof MIXPANEL_CUSTOM_LIB_URL ? MIXPANEL_CUSTOM_LIB_URL : "file:" === f.location.protocol && "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js".match(/^\/\//) ? "https://cdn.mxpnl.com/libs/mixpanel-2-latest.min.js" : "//cdn.mxpnl.com/libs/mixpanel-2-latest.min.js"; g = f.getElementsByTagName("script")[0]; g.parentNode.insertBefore(e, g); } })(document, window.mixpanel || []);
		mixpanel.init( '<?php echo esc_js( $this->token ); ?>', {
			api_host: "https://<?php echo esc_js( self::HOST ); ?>",
			id: false,
			property_blacklist: ['$initial_referrer', '$current_url', '$initial_referring_domain', '$referrer', '$referring_domain']
		} );
		</script><!-- end Mixpanel -->
		<?php
	}
}
