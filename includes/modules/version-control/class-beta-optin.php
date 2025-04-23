<?php
/**
 * The Beta Opt-in functionality.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Beta_Optin class.
 */
class Beta_Optin {

	use Hooker;

	/**
	 * Beta changelog URL.
	 *
	 * @var string
	 */
	const BETA_CHANGELOG_URL = 'https://rankmath.com/changelog/beta/';

	/**
	 * Placeholder for opening tag inserted with JS.
	 *
	 * @var string
	 */
	const NOTICE_START_MARKER = '&#x25B7;';

	/**
	 * Placeholder for closing tag inserted with JS.
	 *
	 * @var string
	 */
	const NOTICE_END_MARKER = '&#x25C1;';

	/**
	 * Holds the fetched trunk version in memory to avoid fetching multiple times.
	 *
	 * @var mixed
	 */
	public $trunk_version = false;

	/**
	 * Actions and filters.
	 *
	 * @return void
	 */
	public function hooks() {
		$this->filter( 'site_transient_update_plugins', 'transient_update_plugins' );
		$this->action( 'in_plugin_update_message-seo-by-rank-math/rank-math.php', 'plugin_update_message' );
		$this->action( 'install_plugins_pre_plugin-information', 'beta_plugin_information' );
		$this->action( 'admin_footer', 'beta_changelog_link_js' );
	}

	/**
	 * Replace plugin info popup for beta versions.
	 */
	public function beta_plugin_information() {
		if ( 'seo-by-rank-math' !== Param::request( 'plugin' ) ) {
			return;
		}

		$transient = get_site_transient( 'update_plugins' );
		if ( self::has_beta_update( $transient ) ) {
			// No-js fallback.
			echo '<html><head></head><body style="margin: 0;"><iframe src="' . esc_attr( self::BETA_CHANGELOG_URL ) . '" style="width: 100%; height: 100%;"></body></html>';
			exit;
		}
	}

	/**
	 * Check if Rank Math update is a beta update in the transient.
	 *
	 * @param  mixed $transient Transient value.
	 * @return boolean          If it is a beta update or not.
	 */
	public static function has_beta_update( $transient ) {
		return (
			is_object( $transient )
			&& ! empty( $transient->response )
			&& ! empty( $transient->response['seo-by-rank-math/rank-math.php'] )
			&& ! empty( $transient->response['seo-by-rank-math/rank-math.php']->is_beta )
		);
	}

	/**
	 * Get all available versions of Rank Math.
	 *
	 * @param boolean $beta  Include beta versions.
	 *
	 * @return array List of versions and download URLs.
	 */
	public static function get_available_versions( $beta = false ) {
		$versions    = [];
		$plugin_info = Version_Control::get_plugin_info();
		if ( empty( $plugin_info['versions'] ) ) {
			return $versions;
		}

		foreach ( (array) $plugin_info['versions'] as $version => $url ) {
			if ( ! self::is_eligible_version( $version, $beta ) ) {
				continue;
			}
			$versions[ $version ] = $url;
		}

		uksort( $versions, 'version_compare' );

		return $versions;
	}

	/**
	 * Check if version should be in the dropdown.
	 *
	 * @param  string  $version Version number.
	 * @param  boolean $beta    If beta versions should be included or not.
	 *
	 * @return boolean          If version should be in the dropdown.
	 */
	public static function is_eligible_version( $version, $beta ) {
		if ( 'trunk' === $version ) {
			return false;
		}

		if ( ! $beta && Str::contains( 'beta', $version ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get latest version available.
	 *
	 * @return string Latest version number.
	 */
	public static function get_latest_version() {
		$plugin_info = Version_Control::get_plugin_info();
		return $plugin_info['version'];
	}

	/**
	 * Get latest beta version available.
	 *
	 * @return string Latest beta version number.
	 */
	public function get_latest_beta_version() {
		$version = get_transient( 'rank_math_trunk_version' );
		if ( ! $version || $this->is_check_requested() ) {
			$version = $this->fetch_trunk_version();
		}

		$beta = 0;
		if ( Str::contains( 'beta', $version ) ) {
			$beta = $version;
		}

		return $beta;
	}

	/**
	 * Fetch latest plugin file from public SVN and get version number.
	 *
	 * @return string
	 */
	public function fetch_trunk_version() {
		if ( false !== $this->trunk_version ) {
			return $this->trunk_version;
		}

		$this->trunk_version = 0;

		$response = wp_remote_get( 'https://plugins.svn.wordpress.org/seo-by-rank-math/trunk/rank-math.php' );
		if ( is_wp_error( $response ) || ! is_array( $response ) ) {
			return $this->trunk_version;
		}

		$plugin_file = wp_remote_retrieve_body( $response );

		preg_match( '/Version:\s+([0-9a-zA-Z.-]+)\s*$/m', $plugin_file, $matches );
		if ( empty( $matches[1] ) ) {
			return $this->trunk_version;
		}

		$this->trunk_version = $matches[1];
		set_transient( 'rank_math_trunk_version', $this->trunk_version, ( 12 * HOUR_IN_SECONDS ) );
		return $this->trunk_version;
	}

	/**
	 * Inject beta in the `update_plugins` transient to be able to update to it.
	 *
	 * @param  mixed $value Original value.
	 *
	 * @return mixed New value.
	 */
	public function transient_update_plugins( $value ) {
		$beta_version = $this->get_latest_beta_version();
		$new_version  = isset( $value->response['seo-by-rank-math/rank-math.php'] ) && ! empty( $value->response['seo-by-rank-math/rank-math.php']->new_version ) ? $value->response['seo-by-rank-math/rank-math.php']->new_version : 0;

		if ( ! $beta_version ) {
			return $value;
		}

		if ( version_compare( $beta_version, rank_math()->version, '>' ) && version_compare( $beta_version, $new_version, '>' ) ) {
			$value = $this->inject_beta( $value, $beta_version );
		}

		return $value;
	}

	/**
	 * Inject beta update in the transient value.
	 *
	 * @param  mixed  $value        Transient value.
	 * @param  string $beta_version Beta version number.
	 *
	 * @return mixed New transient value.
	 */
	public function inject_beta( $value, $beta_version ) {
		if ( empty( $value ) ) {
			$value = new \stdClass();
		}

		if ( empty( $value->response ) ) {
			$value->response = [];
		}

		$value->response['seo-by-rank-math/rank-math.php'] = new \stdClass();

		$plugin_data = Version_Control::get_plugin_data( $beta_version, 'https://downloads.wordpress.org/plugin/seo-by-rank-math.zip' );
		foreach ( $plugin_data as $prop_key => $prop_value ) {
			$value->response['seo-by-rank-math/rank-math.php']->{$prop_key} = $prop_value;
		}

		$value->response['seo-by-rank-math/rank-math.php']->is_beta        = true;
		$value->response['seo-by-rank-math/rank-math.php']->upgrade_notice = self::NOTICE_START_MARKER . ' ' . __( 'This update will install a beta version of Rank Math.', 'rank-math' ) . ' ' . self::NOTICE_END_MARKER;

		if ( empty( $value->no_update ) ) {
			$value->no_update = [];
		}

		unset( $value->no_update['seo-by-rank-math/rank-math.php'] );

		return $value;
	}

	/**
	 * Add warning about beta version in the update notice.
	 *
	 * @param  array $plugin_data An array of plugin metadata.
	 * @return void
	 */
	public function plugin_update_message( $plugin_data ) {
		if ( empty( $plugin_data['is_beta'] ) ) {
			return;
		}

		printf(
			'</p><p class="rank-math-beta-update-notice">%s',
			esc_html__( 'This update will install a beta version of Rank Math.', 'rank-math' )
		);
	}

	/**
	 * Add Javascript to open beta changelog link in a new tab instead of the modal.
	 *
	 * @return void
	 */
	public function beta_changelog_link_js() {
		$screen = get_current_screen();

		$applicable_screens = [
			'update-core',
			'plugins',
			'update-core-network',
			'plugins-network',
		];

		if ( empty( $screen->base ) || ! in_array( $screen->base, $applicable_screens, true ) ) {
			return;
		}

		$transient = get_site_transient( 'update_plugins' );
		if ( ! self::has_beta_update( $transient ) ) {
			return;
		}
		?>
		<script type="text/javascript">
			jQuery( document ).ready( function( $ ) {
				// Change our link.
				$('.open-plugin-details-modal').each( function( index, element ) {
					if ( element.href.indexOf( 'plugin=seo-by-rank-math&section=changelog' ) !== -1 ) {
						// Found our link.
						$( element )
							.removeClass( 'open-plugin-details-modal thickbox' )
							.attr( 'href', '<?php echo esc_js( self::BETA_CHANGELOG_URL ); ?>' )
							.attr( 'target', '_blank' );
						return false;
					}
				} );

				// Change our notice.
				<?php if ( 'update-core' === $screen->base || 'update-core-network' === $screen->base ) { ?>
					$('td.plugin-title').each( function( index, element ) {
						var contents = $( element ).html();
						if ( contents.indexOf( '<?php echo esc_js( html_entity_decode( self::NOTICE_START_MARKER ) ); ?>' ) !== -1 && contents.indexOf( '<?php echo esc_js( html_entity_decode( self::NOTICE_END_MARKER ) ); ?>' ) !== -1 ) {
							contents = contents
								.replace( '<?php echo esc_js( html_entity_decode( self::NOTICE_START_MARKER ) ); ?>', '</p><div class="update-message notice inline notice-warning notice-alt rank-math-beta-update-notice"><p>' )
								.replace( '<?php echo esc_js( html_entity_decode( self::NOTICE_END_MARKER ) ); ?>', '</p></div><p style="display: none;">' );

							$( element ).html( contents );

							return false;
						}
					} );
				<?php } ?>
			} );
		</script>
		<style>
			.update-message.rank-math-beta-update-notice {
				font-weight: bold;
				margin-top: 20px;
			}
			.update-message.rank-math-beta-update-notice > p:before {
				content: "\f534";
			}
		</style>
		<?php
	}

	/**
	 * If user requested check with force-check parameter.
	 *
	 * @return bool
	 */
	public function is_check_requested() {
		return (bool) Param::get( 'force-check' );
	}
}
