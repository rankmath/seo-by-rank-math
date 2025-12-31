<?php
/**
 * The conflicting plugin watcher.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Security;
use RankMath\Helpers\Param;
use RankMath\Helper as GlobalHelper;

defined( 'ABSPATH' ) || exit;

/**
 * Watcher class.
 */
class Watcher implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'maybe_deactivate_plugins' );
		$this->action( 'activated_plugin', 'check_activated_plugin' );
		$this->action( 'deactivated_plugin', 'check_deactivated_plugin' );

		$this->filter( 'wp_helpers_notifications_render', 'deactivate_plugins_link', 10, 3 );
		$this->action( 'update_option_blog_public', 'check_search_engine_visibility' );
	}

	/**
	 * Set/Deactivate conflicting SEO or Sitemap plugins.
	 */
	public function maybe_deactivate_plugins() {
		if ( ! Param::get( 'rank_math_deactivate_plugins' ) ) {
			return;
		}

		if ( ! current_user_can( 'deactivate_plugins' ) ) {
			wp_die( esc_html__( 'Sorry, you are not allowed to deactivate plugins for this site.', 'rank-math' ) );
		}

		check_admin_referer( 'rank_math_deactivate_plugins' );

		$type    = Param::get( 'plugin_type', 'seo', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
		$allowed = [ 'seo', 'sitemap', 'redirections' ];
		if ( ! in_array( $type, $allowed, true ) ) {
			return;
		}

		$this->deactivate_conflicting_plugins( $type );
	}

	/**
	 * Function to run when new plugin is activated.
	 */
	public static function check_activated_plugin() {
		$set     = [];
		$plugins = get_option( 'active_plugins', [] );

		foreach ( self::get_conflicting_plugins() as $plugin => $group ) {
			if ( ! isset( $set[ $group ] ) && in_array( $plugin, $plugins, true ) ) {
				$set[ $group ] = true;
				self::set_notification( $group );
			}
		}

		if ( in_array( 'wpml-string-translation/plugin.php', $plugins, true ) ) {
			GlobalHelper::remove_notification( 'convert_wpml_settings' );
		}
	}

	/**
	 * Function to run when plugin is deactivated.
	 *
	 * @param string $plugin Deactivated plugin path.
	 */
	public function check_deactivated_plugin( $plugin ) {
		$plugins = self::get_conflicting_plugins();
		if ( ! isset( $plugins[ $plugin ] ) ) {
			return;
		}
		$this->remove_notification( $plugins[ $plugin ], $plugin );
	}

	/**
	 * Function to run when Module is enabled/disabled.
	 *
	 * @param string $module Module.
	 * @param string $state  Module state.
	 */
	public static function module_changed( $module, $state ) {
		if ( ! in_array( $module, [ 'sitemap', 'redirections', 'rich-snippet' ], true ) ) {
			return;
		}

		if ( 'off' === $state ) {
			$type = 'sitemap' === $module || 'redirections' === $module ? $module : 'seo';
			GlobalHelper::remove_notification( "conflicting_{$type}_plugins" );
		}

		self::check_activated_plugin();
	}

	/**
	 * Deactivate conflicting plugins.
	 *
	 * @param string $type Plugin type.
	 */
	private function deactivate_conflicting_plugins( $type ) {
		foreach ( self::get_conflicting_plugins() as $plugin => $plugin_type ) {
			if ( $type === $plugin_type && is_plugin_active( $plugin ) ) {
				if ( ! current_user_can( 'deactivate_plugin', $plugin ) ) {
					$message = sprintf(
						/* translators: plugin name */
						esc_html__( 'You are not allowed to deactivate this plugin: %s.', 'rank-math' ),
						esc_html( $plugin )
					);
					GlobalHelper::add_notification(
						$message,
						[
							'type'    => 'error',
							'classes' => 'is-dismissible',
						]
					);
					continue;
				}
				deactivate_plugins( $plugin );
			}
		}

		GlobalHelper::redirect( Security::remove_query_arg_raw( [ 'rank_math_deactivate_plugins', 'plugin_type', '_wpnonce' ] ) );
	}

	/**
	 * Function to set conflict plugin notification.
	 *
	 * @param string $group Plugin group.
	 */
	private static function set_notification( $group ) {
		$message = esc_html__( 'Please keep only one SEO plugin active, otherwise, you might lose your rankings and traffic.', 'rank-math' );

		if ( 'sitemap' === $group ) {
			$message = esc_html__( 'Please keep only one Sitemap plugin active, otherwise, you might lose your rankings and traffic.', 'rank-math' );
		}

		if ( 'redirections' === $group ) {
			$message = esc_html__( 'Please keep only one Redirection plugin active to avoid redirect conflicts or unexpected behavior.', 'rank-math' );
		}

		$message .= ' <a href="###DEACTIVATE_PLUGINS###">' . __( 'Click here to Deactivate', 'rank-math' ) . '</a>';

		GlobalHelper::add_notification(
			$message,
			[
				'id'      => "conflicting_{$group}_plugins",
				'type'    => 'error',
				'classes' => 'is-dismissible',
				'group'   => $group,
			]
		);
	}

	/**
	 * Function to remove conflict plugin notification.
	 *
	 * @param string $type   Plugin type.
	 * @param string $plugin Plugin name.
	 */
	private function remove_notification( $type, $plugin ) {
		foreach ( self::get_conflicting_plugins() as $file => $plugin_type ) {
			if ( $plugin !== $file && $type === $plugin_type && is_plugin_active( $file ) ) {
				return;
			}
		}

		GlobalHelper::remove_notification( "conflicting_{$type}_plugins" );
	}

	/**
	 * Function to get all conflicting plugins.
	 *
	 * @return array
	 */
	private static function get_conflicting_plugins() {
		$plugins = [
			'wordpress-seo/wp-seo.php'                    => 'seo',
			'wordpress-seo-premium/wp-seo-premium.php'    => 'seo',
			'wpseo-local/local-seo.php'                   => 'seo',
			'wpseo-news/wpseo-news.php'                   => 'seo',
			'wpseo-video/video-seo.php'                   => 'seo',
			'all-in-one-seo-pack/all_in_one_seo_pack.php' => 'seo',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php' => 'seo',
			'wp-seopress/seopress.php'                    => 'seo',
			'wp-seopress-pro/seopress-pro.php'            => 'seo',
		];

		if ( GlobalHelper::is_module_active( 'redirections' ) ) {
			$plugins['redirection/redirection.php'] = 'redirections';
		}
		if ( GlobalHelper::is_module_active( 'sitemap' ) ) {
			$plugins['google-sitemap-generator/sitemap.php'] = 'sitemap';
			$plugins['xml-sitemap-feed/xml-sitemap.php']     = 'sitemap';
		}
		if ( GlobalHelper::is_module_active( 'rich-snippet' ) ) {
			$plugins['wp-schema-pro/wp-schema-pro.php']              = 'seo';
			$plugins['all-in-one-schemaorg-rich-snippets/index.php'] = 'seo';
		}
		return $plugins;
	}

	/**
	 * Replace link inside notice dynamically to avoid issues with the nonce.
	 *
	 * @param string $output  Notice output.
	 * @param string $message Notice message.
	 * @param array  $options Notice options.
	 *
	 * @return string
	 */
	public function deactivate_plugins_link( $output, $message, $options ) {
		if ( ! isset( $options['id'] ) || ! preg_match( '/conflicting_.*_plugins/', $options['id'] ) ) {
			return $output;
		}

		$deactivate_url = Security::add_query_arg(
			[
				'rank_math_deactivate_plugins' => '1',
				'plugin_type'                  => $options['group'],
				'_wpnonce'                     => wp_create_nonce( 'rank_math_deactivate_plugins' ),
			],
			admin_url( 'plugins.php' )
		);

		$output = str_replace( '###DEACTIVATE_PLUGINS###', $deactivate_url, $output );

		return $output;
	}

	/**
	 * Check search visibility
	 *
	 * @param integer $value Setting value.
	 */
	public function check_search_engine_visibility( $value ) {

		if ( ! $value ) {
			GlobalHelper::remove_notification( 'search_engine_visibility' );
			return;
		}

		GlobalHelper::add_notification(
			sprintf(
				// translators: %1$s: general reading settings URL.
				__( '<strong>SEO Notice</strong>: Your site is set to No Index and will not appear in search engines. You can change the Search engine visibility <a href="%1$s">from here</a>.', 'rank-math' ),
				admin_url( 'options-reading.php' )
			),
			[
				'type'    => 'warning',
				'id'      => 'search_engine_visibility',
				'classes' => 'is-dismissible',
			]
		);
	}
}
