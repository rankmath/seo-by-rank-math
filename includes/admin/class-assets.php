<?php
/**
 * Register all the necessary CSS and JS.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Google\Console;
use RankMath\Google\Analytics;
use RankMath\Analytics\Url_Inspection;

defined( 'ABSPATH' ) || exit;

/**
 * Assets class.
 *
 * @codeCoverageIgnore
 */
class Assets implements Runner {

	use Hooker;

	/**
	 *  Prefix for the enqueue handles.
	 */
	const PREFIX = 'rank-math-';

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_enqueue_scripts', 'register' );
		$this->action( 'admin_enqueue_scripts', 'enqueue' );
		$this->action( 'admin_enqueue_scripts', 'overwrite_wplink', 99 );

		if ( 'elementor' === \MyThemeShop\Helpers\Param::get( 'action' ) ) {
			$this->action( 'elementor/editor/before_enqueue_scripts', 'register' );
			$this->action( 'elementor/editor/before_enqueue_scripts', 'enqueue' );
			$this->action( 'elementor/editor/before_enqueue_scripts', 'overwrite_wplink', 99 );
		}
	}

	/**
	 * Register styles and scripts.
	 */
	public function register() {
		$js     = rank_math()->plugin_url() . 'assets/admin/js/';
		$css    = rank_math()->plugin_url() . 'assets/admin/css/';
		$vendor = rank_math()->plugin_url() . 'assets/vendor/';

		// Styles.
		wp_register_style( self::PREFIX . 'common', $css . 'common.css', null, rank_math()->version );
		wp_register_style( self::PREFIX . 'cmb2', $css . 'cmb2.css', null, rank_math()->version );
		wp_register_style( self::PREFIX . 'dashboard', $css . 'dashboard.css', [ 'rank-math-common' ], rank_math()->version );
		wp_register_style( self::PREFIX . 'dashboard-widget', $css . 'dashboard-widget.css', null, rank_math()->version );

		// Scripts.
		wp_register_script( self::PREFIX . 'common', $js . 'common.js', [ 'jquery', 'wp-i18n', 'lodash' ], rank_math()->version, true );
		wp_register_script( self::PREFIX . 'dashboard', $js . 'dashboard.js', [ 'jquery', 'clipboard' ], rank_math()->version, true );

		// Select2.
		wp_register_style( 'select2-rm', $vendor . 'select2/select2.min.css', null, '4.0.6-rc.1' );
		wp_register_script( 'select2-rm', $vendor . 'select2/select2.min.js', null, '4.0.6-rc.1', true );

		// Inline script for core admin page Settings > Permalinks.
		wp_register_script( self::PREFIX . 'core-permalink-settings', '' ); // phpcs:ignore
		wp_add_inline_script( self::PREFIX . 'core-permalink-settings', $this->get_permalinks_inline_script() );

		if ( ! wp_script_is( 'wp-hooks', 'registered' ) ) {
			wp_register_script( 'wp-hooks', rank_math()->plugin_url() . 'assets/vendor/hooks.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'wp-wordcount', 'registered' ) ) {
			wp_register_script( 'wp-wordcount', rank_math()->plugin_url() . 'assets/vendor/wordcount.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'wp-autop', 'registered' ) ) {
			wp_register_script( 'wp-autop', rank_math()->plugin_url() . 'assets/vendor/autop.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'wp-url', 'registered' ) ) {
			wp_register_script( 'wp-url', rank_math()->plugin_url() . 'assets/vendor/url.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'wp-i18n', 'registered' ) ) {
			wp_register_script( 'wp-i18n', rank_math()->plugin_url() . 'assets/vendor/i18n.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'clipboard', 'registered' ) ) {
			wp_register_script( 'clipboard', rank_math()->plugin_url() . 'assets/vendor/clipboard.min.js', [], rank_math()->version, true );
		}

		if ( ! wp_script_is( 'lodash', 'registered' ) ) {
			wp_register_script( 'lodash', rank_math()->plugin_url() . 'assets/vendor/lodash.js', [], rank_math()->version );
			wp_add_inline_script( 'lodash', 'window.lodash = _.noConflict();' );
		}

		Helper::add_json(
			'api',
			[
				'root'  => esc_url_raw( get_rest_url() ),
				'nonce' => ( wp_installing() && ! is_multisite() ) ? '' : wp_create_nonce( 'wp_rest' ),
			]
		);

		Helper::add_json(
			'keywordsApi',
			[
				'url' => 'https://api.rankmath.com/ltkw/v1/',
			]
		);

		Helper::add_json(
			'links',
			KB::get_links()
		);

		Helper::add_json(
			'validationl10n',
			[
				'regexErrorDefault'    => __( 'Please use the correct format.', 'rank-math' ),
				'requiredErrorDefault' => __( 'This field is required.', 'rank-math' ),
				'emailErrorDefault'    => __( 'Please enter a valid email address.', 'rank-math' ),
				'urlErrorDefault'      => __( 'Please enter a valid URL.', 'rank-math' ),
			]
		);

		Helper::add_json( 'capitalizeTitle', Helper::get_settings( 'titles.capitalize_titles' ) );

		Helper::add_json( 'isConsoleConnected', Console::is_console_connected() );
		Helper::add_json( 'isAnalyticsConnected', Analytics::is_analytics_connected() );
		Helper::add_json( 'isUrlInspectionEnabled', Url_Inspection::is_enabled() );

		/**
		 * Allow other plugins to register/deregister admin styles or scripts after plugin assets.
		 */
		$this->do_action( 'admin/register_scripts' );

	}

	/**
	 * Enqueue styles and scripts.
	 */
	public function enqueue() {
		$screen = get_current_screen();

		if ( 'dashboard' === $screen->id ) {
			wp_enqueue_style( self::PREFIX . 'dashboard-widget' );
			wp_enqueue_script( self::PREFIX . 'dashboard' );
		}

		// Our screens only.
		if ( ! in_array( $screen->taxonomy, Helper::get_allowed_taxonomies(), true ) && ! in_array( $screen->id, $this->get_admin_screen_ids(), true ) ) {
			return;
		}

		// Add thank you.
		$this->filter( 'admin_footer_text', 'admin_footer_text' );

		/**
		 * Allow other plugins to enqueue/dequeue admin styles or scripts after plugin assets.
		 */
		$this->do_action( 'admin/enqueue_scripts' );
	}

	/**
	 * Add footer credit on admin pages.
	 *
	 * @param string $text Default text for admin footer.
	 * @return string
	 */
	public function admin_footer_text( $text ) {
		/* translators: plugin url */
		return Helper::is_whitelabel() ? $text : sprintf( wp_kses_post( __( 'Thank you for using <a href="%s" target="_blank">Rank Math</a>', 'rank-math' ) ), KB::get( 'seo-suite', 'Admin Footer Text' ) );
	}

	/**
	 * Overwrite wplink script file.
	 * Rank Math adds new options in the link popup when editing a post.
	 */
	public function overwrite_wplink() {

		wp_deregister_script( 'wplink' );
		wp_register_script( 'wplink', rank_math()->plugin_url() . 'assets/admin/js/wplink.js', [ 'jquery', 'wp-a11y' ], rank_math()->version, true );

		wp_localize_script(
			'wplink',
			'wpLinkL10n',
			[
				'title'             => esc_html__( 'Insert/edit link', 'rank-math' ),
				'update'            => esc_html__( 'Update', 'rank-math' ),
				'save'              => esc_html__( 'Add Link', 'rank-math' ),
				'noTitle'           => esc_html__( '(no title)', 'rank-math' ),
				'noMatchesFound'    => esc_html__( 'No matches found.', 'rank-math' ),
				'linkSelected'      => esc_html__( 'Link selected.', 'rank-math' ),
				'linkInserted'      => esc_html__( 'Link inserted.', 'rank-math' ),
				'relCheckbox'       => __( 'Add <code>rel="nofollow"</code>', 'rank-math' ),
				'sponsoredCheckbox' => __( 'Add <code>rel="sponsored"</code>', 'rank-math' ),
				'linkTitle'         => esc_html__( 'Link Title', 'rank-math' ),
			]
		);
	}

	/**
	 * Enqueues styles.
	 *
	 * @param string $style Name of the style.
	 */
	public function enqueue_style( $style ) {
		wp_enqueue_style( self::PREFIX . $style );
	}

	/**
	 * Enqueues scripts.
	 *
	 * @param string $script Name of the script.
	 */
	public function enqueue_script( $script ) {
		wp_enqueue_script( self::PREFIX . $script );
	}

	/**
	 * Get admin screen ids.
	 *
	 * @return array
	 */
	private function get_admin_screen_ids() {
		$pages = [
			'toplevel_page_rank-math',
			'rank-math_page_rank-math-role-manager',
			'rank-math_page_rank-math-seo-analysis',
			'rank-math_page_rank-math-404-monitor',
			'rank-math_page_rank-math-redirections',
			'rank-math_page_rank-math-link-builder',
			'rank-math_page_rank-math-analytics',
			'rank-math_page_rank-math-import-export',
			'rank-math_page_rank-math-help',
			'user-edit',
			'profile',
			'rank_math_schema',
		];

		return array_merge( $pages, Helper::get_allowed_post_types() );
	}

	/**
	 * Inline script to warn the user about the risks of changing the permalinks on a live site.
	 *
	 * @return string
	 */
	public function get_permalinks_inline_script() {
		// Don't add the script if site is set to noindex.
		if ( ! get_option( 'blog_public' ) ) {
			return '';
		}

		return "jQuery( window ).load( function() {
			var noticeShown = false;
			var showNotice = function() {
				if ( noticeShown ) {
					return true;
				}
				jQuery( '.rank-math-notice-permalinks-warning' ).removeClass( 'hidden' ).insertBefore( 'p.submit' );
				noticeShown = true;
				return true;
			}

			jQuery( '.available-structure-tags button' ).on( 'click', showNotice );
			jQuery( 'input[type=text], input[type=radio]' ).on( 'focus change', showNotice );
		} );";
	}
}
