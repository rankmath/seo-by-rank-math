<?php
/**
 * The GTag
 *
 * @since      1.0.49
 * @package    RankMath
 * @subpackage RankMath\modules
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright 2019 Google LLC
 * The following code is a derivative work of the code from the Site Kit Plugin(https://sitekit.withgoogle.com), which is licensed under Apache License 2.0.
 */

namespace RankMath\Analytics;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use AMP_Theme_Support;
use AMP_Options_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * GTag class.
 */
class GTag {

	use Hooker;

	/**
	 * Primary "standard" AMP website mode.
	 *
	 * @var string
	 */
	const AMP_MODE_PRIMARY = 'primary';

	/**
	 * Secondary AMP website mode.
	 *
	 * @var string
	 */
	const AMP_MODE_SECONDARY = 'secondary';

	/**
	 * Options.
	 *
	 * @var array
	 */
	private $options = null;

	/**
	 * Internal flag set after gtag amp print for the first time.
	 *
	 * @var bool
	 */
	private $did_amp_gtag = false;

	/**
	 * The Constructor
	 */
	public function __construct() {
		$this->action( 'template_redirect', 'add_analytics_tag' );
	}

	/**
	 * Add analytics tag.
	 */
	public function add_analytics_tag() {
		// Early Bail!!
		$use_snippet = $this->get( 'install_code' );
		if ( ! $use_snippet ) {
			return;
		}

		$property_id = $this->get( 'property_id' );
		if ( ! $property_id ) {
			return;
		}

		$this->action( 'wp_head', 'print_tracking_opt_out', 0 ); // For non-AMP and AMP.
		$this->action( 'web_stories_story_head', 'print_tracking_opt_out', 0 ); // For Web Stories plugin.

		if ( $this->is_amp() ) {
			$this->action( 'amp_print_analytics', 'print_amp_gtag' ); // For all AMP modes.
			$this->action( 'wp_footer', 'print_amp_gtag', 20 ); // For AMP Standard and Transitional.
			$this->action( 'amp_post_template_footer', 'print_amp_gtag', 20 ); // For AMP Reader.
			$this->action( 'web_stories_print_analytics', 'print_amp_gtag' ); // For Web Stories plugin.

			// Load amp-analytics component for AMP Reader.
			$this->filter( 'amp_post_template_data', 'amp_analytics_component_data' );
		} else {
			// For non-AMP. If current WordPress verion is 5.7 or above, use core function introducted from WordPress 5.7 and add async loading to gtag script.
			if ( version_compare( get_bloginfo( 'version' ), '5.7', '<' ) ) {
				$this->action( 'wp_enqueue_scripts', 'enqueue_gtag_js' );
			} else {
				$this->action( 'wp_head', 'add_gtag_js' );
			}
		}
	}

	/**
	 * Print gtag <amp-analytics> tag.
	 */
	public function print_amp_gtag() {
		if ( $this->did_amp_gtag ) {
			return;
		}

		$this->did_amp_gtag = true;

		$property_id  = $this->get( 'property_id' );
		$gtag_options = [
			'vars'            => [
				'gtag_id' => $property_id,
				'config'  => [
					$property_id => [
						'groups' => 'default',
						'linker' => [
							'domains' => [ $this->get_home_domain() ],
						],
					],
				],
			],
			'optoutElementId' => '__gaOptOutExtension',
		];
		?>
		<amp-analytics type="gtag" data-credentials="include">
			<script type="application/json">
				<?php echo wp_json_encode( $gtag_options ); ?>
			</script>
		</amp-analytics>
		<?php
	}

	/**
	 * Loads AMP analytics script if opted in.
	 *
	 * @param array $data AMP template data.
	 * @return array Filtered $data.
	 */
	public function amp_analytics_component_data( $data ) {
		if ( isset( $data['amp_component_scripts']['amp-analytics'] ) ) {
			return $data;
		}

		$data['amp_component_scripts']['amp-analytics'] = 'https://cdn.ampproject.org/v0/amp-analytics-0.1.js';
		return $data;
	}

	/**
	 * Print gtag snippet for non-amp. Used only for WordPress 5.7 or above.
	 */
	public function add_gtag_js() {
		if ( $this->is_tracking_disabled() ) {
			return;
		}

		$gtag_script_info = $this->get_gtag_info();

		wp_print_script_tag(
			[
				'id'    => 'google_gtagjs',
				'src'   => $gtag_script_info['url'],
				'async' => true,
			]
		);

		wp_print_inline_script_tag(
			$gtag_script_info['inline'],
			[
				'id' => 'google_gtagjs-inline',
			]
		);
	}

	/**
	 * Print gtag snippet for non-amp. Used for below WordPress 5.7.
	 */
	public function enqueue_gtag_js() {
		if ( $this->is_tracking_disabled() ) {
			return;
		}

		$gtag_script_info = $this->get_gtag_info();

		wp_enqueue_script( // phpcs:ignore WordPress.WP.EnqueuedResourceParameters.MissingVersion
			'google_gtagjs',
			$gtag_script_info['url'],
			false,
			null,
			false
		);

		wp_add_inline_script(
			'google_gtagjs',
			$gtag_script_info['inline']
		);
	}

	/**
	 * Gets the current AMP mode.
	 *
	 * @return bool|string 'primary' if in standard mode,
	 *                     'secondary' if in transitional or reader modes
	 *                     false if AMP not active, or unknown mode
	 */
	public function get_amp_mode() {
		if ( ! class_exists( 'AMP_Theme_Support' ) ) {
			return false;
		}

		$exposes_support_mode = defined( 'AMP_Theme_Support::STANDARD_MODE_SLUG' )
			&& defined( 'AMP_Theme_Support::TRANSITIONAL_MODE_SLUG' )
			&& defined( 'AMP_Theme_Support::READER_MODE_SLUG' );

		if ( defined( 'AMP__VERSION' ) ) {
			$amp_plugin_version = AMP__VERSION;
			if ( strpos( $amp_plugin_version, '-' ) !== false ) {
				$amp_plugin_version = explode( '-', $amp_plugin_version )[0];
			}

			$amp_plugin_version_2_or_higher = version_compare( $amp_plugin_version, '2.0.0', '>=' );
		} else {
			$amp_plugin_version_2_or_higher = false;
		}

		if ( $amp_plugin_version_2_or_higher ) {
			$exposes_support_mode = class_exists( 'AMP_Options_Manager' )
				&& method_exists( 'AMP_Options_Manager', 'get_option' )
				&& $exposes_support_mode;
		} else {
			$exposes_support_mode = class_exists( 'AMP_Theme_Support' )
				&& method_exists( 'AMP_Theme_Support', 'get_support_mode' )
				&& $exposes_support_mode;
		}

		if ( $exposes_support_mode ) {
			// If recent version, we can properly detect the mode.
			if ( $amp_plugin_version_2_or_higher ) {
				$mode = AMP_Options_Manager::get_option( 'theme_support' );
			} else {
				$mode = AMP_Theme_Support::get_support_mode();
			}

			if ( AMP_Theme_Support::STANDARD_MODE_SLUG === $mode ) {
				return self::AMP_MODE_PRIMARY;
			}

			if ( in_array( $mode, [ AMP_Theme_Support::TRANSITIONAL_MODE_SLUG, AMP_Theme_Support::READER_MODE_SLUG ], true ) ) {
				return self::AMP_MODE_SECONDARY;
			}
		} elseif ( function_exists( 'amp_is_canonical' ) ) {
			// On older versions, if it is not primary AMP, it is definitely secondary AMP (transitional or reader mode).
			if ( amp_is_canonical() ) {
				return self::AMP_MODE_PRIMARY;
			}

			return self::AMP_MODE_SECONDARY;
		}

		return false;
	}

	/**
	 * Is AMP url.
	 *
	 * @return bool
	 */
	protected function is_amp() {
		if ( is_singular( 'web-story' ) ) {
			return true;
		}

		return function_exists( 'is_amp_endpoint' ) && is_amp_endpoint();
	}

	/**
	 * Is tracking disabled.
	 *
	 * @return bool
	 */
	protected function is_tracking_disabled() {
		if ( ! $this->get( 'exclude_loggedin' ) ) {
			return false;
		}

		$logged_in    = is_user_logged_in();
		$filter_match = false;
		if ( $logged_in ) {
			if ( ! function_exists( 'get_editable_roles' ) ) {
				require_once ABSPATH . 'wp-admin/includes/user.php';
			}

			$all_roles    = array_keys( get_editable_roles() );
			$all_roles    = array_combine( $all_roles, $all_roles ); // Copy values to keys for easier filtering.
			$user_roles   = array_flip( get_userdata( get_current_user_id() )->roles );
			$filter_match = count( array_intersect_key( (array) $this->do_filter( 'analytics/gtag_exclude_loggedin_roles', $all_roles ), $user_roles ) );
		}

		return $filter_match;
	}

	/**
	 * Gets the hostname of the home URL.
	 *
	 * @return string
	 */
	private function get_home_domain() {
		return wp_parse_url( home_url(), PHP_URL_HOST );
	}

	/**
	 * Get option
	 *
	 * @param  string $id Option to get.
	 *
	 * @return mixed
	 */
	protected function get( $id ) {
		if ( is_null( $this->options ) ) {
			$this->options = $this->normalize_it( get_option( 'rank_math_google_analytic_options', [] ) );
		}

		return isset( $this->options[ $id ] ) ? $this->options[ $id ] : false;
	}

	/**
	 * Get gtag script info
	 *
	 * @return mixed
	 */
	protected function get_gtag_info() {
		// Get Google Analytics Property ID.
		$property_id = $this->get( 'property_id' );

		// Get main gtag script Url.
		$url = 'https://www.googletagmanager.com/gtag/js?id=' . esc_attr( $property_id );

		$gtag_opt = [];
		if ( $this->get_amp_mode() ) {
			$gtag_opt['linker'] = [
				'domains' => [ $this->get_home_domain() ],
			];
		}

		$gtag_inline_linker_script = '';
		if ( ! empty( $gtag_opt['linker'] ) ) {
			$gtag_inline_linker_script = 'gtag(\'set\', \'linker\', ' . wp_json_encode( $gtag_opt['linker'] ) . ' );';
		}
		unset( $gtag_opt['linker'] );

		// Get Google Analytics Property ID.
		$gtag_config = [];
		$gtag_config = $this->do_filter( 'analytics/gtag_config', $gtag_config );

		// Construct inline scripts.
		$gtag_inline_script  = 'window.dataLayer = window.dataLayer || [];function gtag(){dataLayer.push(arguments);}';
		$gtag_inline_script .= $gtag_inline_linker_script;
		$gtag_inline_script .= 'gtag(\'js\', new Date());';
		$gtag_inline_script .= 'gtag(\'config\', \'' . esc_attr( $property_id ) . '\', {' . join( ', ', $gtag_config ) . '} );';

		$gtag = $this->do_filter(
			'analytics/gtag',
			[
				'url'    => $url,
				'inline' => $gtag_inline_script,
			]
		);

		return $gtag;
	}

	/**
	 * Normalize option data
	 *
	 * @param mixed $options Array to normalize.
	 * @return mixed
	 */
	protected function normalize_it( $options ) {
		foreach ( (array) $options as $key => $value ) {
			$options[ $key ] = is_array( $value ) ? $this->normalize_it( $value ) : Helper::normalize_data( $value );
		}

		return $options;
	}

	/**
	 * Print the user tracking opt-out code
	 *
	 * This script opts out of all Google Analytics tracking, for all measurement IDs, regardless of implementation.
	 *
	 * @link https://developers.google.com/analytics/devguides/collection/analyticsjs/user-opt-out
	 */
	public function print_tracking_opt_out() {
		if ( ! $this->is_tracking_disabled() ) {
			return;
		}

		if ( $this->is_amp() ) :
			?>
		<script type="application/ld+json" id="__gaOptOutExtension"></script>
		<?php else : ?>
		<script type="text/javascript">window['ga-disable-<?php echo esc_js( $this->get( 'property_id' ) ); ?>'] = true;</script>
			<?php
		endif;
	}
}
