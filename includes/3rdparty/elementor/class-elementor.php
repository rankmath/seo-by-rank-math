<?php
/**
 * Elementor integration.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Elementor;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Editor;

defined( 'ABSPATH' ) || exit;

/**
 * Elementor class.
 */
class Elementor {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'init', 'init' );
		$this->filter( 'rank_math/frontend/robots', 'robots' );
		$this->filter( 'rank_math/frontend/disable_integration', 'disable_frontend_integration' );
	}

	/**
	 * Intialize.
	 */
	public function init() {
		if ( ! $this->can_add_seo_tab() ) {
			return;
		}

		$this->action( 'elementor/editor/before_enqueue_scripts', 'enqueue' );
		add_action( 'elementor/editor/footer', [ rank_math()->json, 'output' ], 0 );
		$this->action( 'elementor/editor/footer', 'start_capturing', 0 );
		$this->action( 'elementor/editor/footer', 'end_capturing', 999 );
		$this->filter( 'rank_math/sitemap/content_before_parse_html_images', 'apply_builder_in_content', 10, 2 );
	}

	/**
	 * Disable frontend integration on Elementor Maintenance page.
	 *
	 * @since 1.0.91
	 *
	 * @param boolean $value Whether to run the frontend integration.
	 */
	public function disable_frontend_integration( $value ) {
		$mode = get_option( 'elementor_maintenance_mode_mode' );
		if ( ! in_array( $mode, [ 'maintenance', 'coming_soon' ], true ) ) {
			return $value;
		}

		if ( ! get_option( 'elementor_maintenance_mode_template_id' ) ) {
			return $value;
		}

		$exclude_mode = get_option( 'elementor_maintenance_mode_exclude_mode', [] );
		if ( 'logged_in' === $exclude_mode && is_user_logged_in() ) {
			return $value;
		}

		if ( 'custom' !== $exclude_mode ) {
			return true;
		}

		$exclude_roles = get_option( 'elementor_maintenance_mode_exclude_roles', [] );
		$user          = wp_get_current_user();
		$user_roles    = $user->roles;

		if ( is_multisite() && is_super_admin() ) {
			$user_roles[] = 'super_admin';
		}

		$compare_roles = array_intersect( $user_roles, $exclude_roles );

		return ! empty( $compare_roles ) ? $value : true;
	}

	/**
	 * Start capturing buffer.
	 */
	public function start_capturing() {
		ob_start();
	}

	/**
	 * End capturing buffer and add button.
	 */
	public function end_capturing() {
		$output  = \ob_get_clean();
		$search  = '/(<div class="elementor-component-tab elementor-panel-navigation-tab" data-tab="global">.*<\/div>)/m';
		$replace = '${1}<div class="elementor-component-tab elementor-panel-navigation-tab" data-tab="rank-math">SEO</div>';
		echo \preg_replace(
			$search,
			$replace,
			$output
		);
	}

	/**
	 * Enqueue scripts.
	 */
	public function enqueue() {
		$deps = [
			'wp-core-data',
			'wp-components',
			'wp-block-editor',
			'wp-element',
			'wp-data',
			'wp-api-fetch',
			'wp-media-utils',
			'site-health',
			'rank-math-analyzer',
			'backbone-marionette',
			'elementor-common-modules',
			'rank-math-app',
		];

		$mode = \Elementor\Core\Settings\Manager::get_settings_managers( 'editorPreferences' )->get_model()->get_settings( 'ui_theme' );
		wp_deregister_style( 'rank-math-editor' );

		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'site-health' );
		wp_enqueue_style( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/css/elementor.css', [], rank_math()->version );
		$media_query = '';

		$dark_styles = $this->do_filter(
			'elementor/dark_styles',
			[
				'rank-math-elementor-dark' => rank_math()->plugin_url() . 'assets/admin/css/elementor-dark.css',
			]
		);

		if ( 'light' !== $mode ) {
			$media_query = 'auto' === $mode ? '(prefers-color-scheme: dark)' : 'all';
			foreach ( $dark_styles as $handle => $src ) {
				wp_enqueue_style( $handle, $src, [], rank_math()->version, $media_query );
			}
		}

		Helper::add_json( 'elementorDarkMode', $dark_styles );

		wp_enqueue_script( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/js/elementor.js', $deps, rank_math()->version, true );
		rank_math()->variables->setup();
		rank_math()->variables->setup_json();
	}

	/**
	 * Filters the post content before it is parsed for Sitmeap images..
	 * Used to apply the Elementor page editor on the post content.
	 *
	 * @since 1.0.38
	 *
	 * @param string $content The post content.
	 * @param int    $post_id The post ID.
	 *
	 * @return string The post content.
	 */
	public function apply_builder_in_content( $content, $post_id ) {
		if ( \Elementor\Plugin::$instance->db->is_built_with_elementor( $post_id ) ) {
			return \Elementor\Plugin::$instance->frontend->get_builder_content( $post_id );
		}

		return $content;
	}

	/**
	 * Add SEO tab in Elementor Page Builder.
	 *
	 * @return bool
	 */
	private function can_add_seo_tab() {
		/**
		 * Filter to show/hide SEO Tab in the Elementor Editor.
		 */
		if ( ! $this->do_filter( 'elementor/add_seo_tab', true ) ) {
			return false;
		}

		$post_type = isset( $_GET['post'] ) ? get_post_type( $_GET['post'] ) : '';
		if ( $post_type && ! Helper::get_settings( 'titles.pt_' . $post_type . '_add_meta_box' ) ) {
			return false;
		}

		return Editor::can_add_editor();
	}

	/**
	 * Change robots for Elementor Templates pages
	 *
	 * @param array $robots Array of robots to sanitize.
	 *
	 * @return array Modified robots.
	 */
	public function robots( $robots ) {
		if ( is_singular( 'elementor_library' ) ) {
			$robots['index']  = 'noindex';
			$robots['follow'] = 'nofollow';
		}

		return $robots;
	}
}
