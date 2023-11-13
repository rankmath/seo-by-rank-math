<?php
/**
 * Divi integration.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Divi;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Schema\DB as Schema_DB;
use RankMath\Schema\Admin as Schema_Admin;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Editor;
use RankMath\Admin\Metabox\Screen;
use WP_Dependencies;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Divi class.
 */
class Divi {

	use Hooker;

	/**
	 * Screen object.
	 *
	 * @var Screen
	 */
	private $screen;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'wp', 'init' );
	}

	/**
	 * Intialize.
	 */
	public function init() {
		if ( ! $this->can_add_seo_tab() ) {
			return;
		}

		$this->screen = new Screen();
		$this->screen->load_screen( 'post' );

		$this->action( 'template_redirect', 'set_window_lodash', 0 );
		$this->action( 'wp_enqueue_scripts', 'register_rankmath_react' );
		$this->action( 'wp_enqueue_scripts', 'add_json_data', 0 );
		$this->action( 'wp_footer', 'footer_enqueue_scripts', 11 );
		remove_action( 'wp_footer', [ rank_math()->json, 'output' ], 0 );
		add_action( 'wp_footer', [ rank_math()->json, 'output' ], 11 );
		$this->filter( 'script_loader_tag', 'add_et_tag', 10, 3 );
	}

	/**
	 * Set the global lodash variable.
	 *
	 * Lodash's `noConflict` would prevent UnderscoreJS from taking over the underscore (_)
	 * global variable. Because Underscore.js will later also be assigned to the underscore (_)
	 * global this function should run as early as possible.
	 */
	public function set_window_lodash() {
		wp_register_script( 'rm-set-window-lodash', '', [ 'lodash' ], rank_math()->version, false );
		wp_enqueue_script( 'rm-set-window-lodash' );
		wp_add_inline_script(
			'rm-set-window-lodash',
			join(
				"\r\n ",
				[
					'window.isLodash = function() {',
					"if ( typeof window._ !== 'function' || typeof window._.forEach !== 'function' ) {",
					'return false;',
					'}',
					'var isLodash = true;',
					'window._.forEach(',
					"[ 'cloneDeep', 'at', 'add', 'ary', 'attempt' ],",
					'function( fn ) {',
					"if ( isLodash && typeof window._[ fn ] !== 'function' ) {",
					'isLodash = false;',
					'}',
					'}',
					');',
					'return isLodash;',
					'}',
					'if ( window.isLodash() ) { window.lodash = window._.noConflict(); }',
				]
			)
		);
	}

	/**
	 * Register RankMath React and ReactDOM.
	 *
	 * Registers the native WP version of react with a custom handle for use in the
	 * RankMath module. Divi builder dequeues and deregisters native WP react scripts
	 * and replaces them with their own copy of React. Their copy might not be of the
	 * same version as the one RankMath requires.
	 */
	public function register_rankmath_react() {
		$path   = site_url( '/wp-includes/js/dist/vendor/' );
		$suffix = wp_scripts_get_suffix();
		wp_register_script( 'rm-react', "{$path}react{$suffix}.js", [ 'wp-polyfill', 'react' ], '16.13.1', true );
		wp_register_script( 'rm-react-dom', "{$path}react-dom{$suffix}.js", [ 'rm-react', 'react-dom' ], '16.13.1', true );
	}

	/**
	 * Add JSON data.
	 */
	public function add_json_data() {

		if ( Helper::has_cap( 'onpage_snippet' ) ) {

			// Schema.
			$schemas = $this->get_schema_data( get_the_ID() );
			Helper::add_json( 'schemas', $schemas );
			Helper::add_json( 'customSchemaImage', esc_url( rank_math()->plugin_url() . 'includes/modules/schema/assets/img/custom-schema-builder.jpg' ) );

			// Trends.
			$trends_upgrade_link = KB::get( 'pro', 'Divi General Tab Trends' );
			Helper::add_json( 'trendsUpgradeLink', esc_url_raw( $trends_upgrade_link ) );
			Helper::add_json( 'trendsPreviewImage', esc_url( rank_math()->plugin_url() . 'assets/admin/img/trends-preview.jpg' ) );
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

		Helper::add_json( 'links', KB::get_links() );

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
		Helper::add_json( 'blogName', get_bloginfo( 'name' ) );

		if ( is_admin_bar_showing() && Helper::has_cap( 'admin_bar' ) ) {
			Helper::add_json( 'objectID', get_the_ID() );
			Helper::add_json( 'objectType', 'post' );
		}
	}

	/**
	 * Enqueue scripts.
	 */
	public function footer_enqueue_scripts() {
		/**
		 * Allow other plugins to enqueue/dequeue admin styles or scripts before plugin assets.
		 */
		$this->do_action( 'admin/before_editor_scripts' );

		$divi_deps = [
			'jquery',
			'lodash',
			'rm-react',
			'rm-react-dom',
			'rm-set-window-lodash',
			'et-dynamic-asset-helpers',
			'wp-api-fetch',
			'wp-block-editor',
			'wp-components',
			'wp-compose',
			'wp-core-data',
			'wp-data',
			'wp-element',
			'wp-hooks',
			'wp-media-utils',
			'rank-math-analyzer',
			'rank-math-app',
		];

		if ( is_admin_bar_showing() && Helper::has_cap( 'admin_bar' ) ) {
			wp_enqueue_style( 'rank-math', rank_math()->assets() . 'css/rank-math.css', null, rank_math()->version );
			wp_enqueue_script( 'rank-math', rank_math()->assets() . 'js/rank-math.js', [ 'jquery' ], rank_math()->version, true );
		}

		wp_enqueue_style( 'rank-math-common', rank_math()->plugin_url() . 'assets/admin/css/common.css', null, rank_math()->version );
		wp_enqueue_style( 'wp-components' );
		wp_enqueue_style( 'rank-math-editor', rank_math()->plugin_url() . 'includes/3rdparty/divi/assets/css/divi.css', [], rank_math()->version );

		wp_register_script( 'rank-math-analyzer', rank_math()->plugin_url() . 'assets/admin/js/analyzer.js', null, rank_math()->version, true );
		wp_enqueue_script( 'rank-math-editor', rank_math()->plugin_url() . 'includes/3rdparty/divi/assets/js/divi.js', $divi_deps, rank_math()->version, true );
		wp_enqueue_script( 'rank-math-divi-iframe', rank_math()->plugin_url() . 'includes/3rdparty/divi/assets/js/divi-iframe.js', [ 'jquery', 'lodash' ], rank_math()->version, true );

		if ( Helper::is_module_active( 'rich-snippet' ) ) {
			wp_enqueue_style( 'rank-math-schema', rank_math()->plugin_url() . 'includes/modules/schema/assets/css/schema.css', [ 'wp-components' ], rank_math()->version );

			wp_enqueue_script( 'rank-math-schema', rank_math()->plugin_url() . 'includes/modules/schema/assets/js/schema-gutenberg.js', [ 'rank-math-editor' ], rank_math()->version, true );
			wp_set_script_translations( 'rank-math-schema', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
		}

		rank_math()->variables->setup();
		rank_math()->variables->setup_json();

		$this->screen->localize();

		$this->print_react_containers();

		/**
		 * Allow other plugins to enqueue/dequeue admin styles or scripts after plugin assets.
		 */
		$this->do_action( 'admin/editor_scripts' );
	}

	/**
	 * Add et attributes to script tags.
	 *
	 * @param string $tag The <script> tag for the enqueued script.
	 * @param string $handle The script's registered handle.
	 * @param string $src The script's source URL.
	 *
	 * @return string
	 */
	public function add_et_tag( $tag, $handle, $src ) {
		$script_handles = [
			'rm-react',
			'rm-react-dom',
			'lodash',
			'moment',
			'rank-math',
			'rank-math-analyzer',
			'rank-math-schema',
			'rank-math-editor',
			'rank-math-content-ai',
			'rank-math-app',
			// Scripts required by pro version.
			'wp-plugins',
			'jquery-ui-autocomplete',
			'rank-math-pro-editor',
			'rank-math-schema-pro',
			'rank-math-pro-schema-filters',
			'rank-math-pro-news',
		];

		$exclude_handles = [
			'wp-util',
			'wp-backbone',
			'wp-plupload',
			'wp-mediaelement',
			'wp-color-picker',
			'wp-color-picker-alpha',
			'wp-embed',
			'wp-hooks',
		];

		if ( in_array( $handle, $exclude_handles, true ) ) {
			return $tag;
		}

		if ( Str::starts_with( 'wp-', $handle ) || in_array( $handle, $script_handles, true ) ) {
			// These tags load in parent window only, not in Divi iframe.
			return '<script type="text/javascript" src="' . $src . '" class="et_fb_ignore_iframe"></script>' . "\n"; // phpcs:ignore
		}

		return $tag;
	}

	/**
	 * Print React containers onto the screen.
	 */
	public function print_react_containers() {
		echo '<div id="rank-math-rm-app-root" class="et_fb_ignore_iframe"></div>';
		echo '<div id="rank-math-rm-settings-bar-root" class="et_fb_ignore_iframe"></div>';
	}


	/**
	 * Can add SEO in Divi Page Builder.
	 *
	 * @return bool
	 */
	private function can_add_seo_tab() {
		if (
			! Helper::is_divi_frontend_editor() ||
			! defined( 'ET_BUILDER_PRODUCT_VERSION' ) ||
			! version_compare( '4.9.2', ET_BUILDER_PRODUCT_VERSION, 'le' )
		) {
			return false;
		}

		/**
		 * Filter to show/hide SEO Tab in Divi Editor.
		 */
		if ( ! $this->do_filter( 'divi/add_seo_tab', true ) ) {
			return false;
		}

		$post_type = get_post_type();
		if ( $post_type && ! Helper::get_settings( 'titles.pt_' . $post_type . '_add_meta_box' ) ) {
			return false;
		}

		return Editor::can_add_editor();
	}

	/**
	 * Get Schema Data.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return array $schemas Schema Data.
	 */
	private function get_schema_data( $post_id ) {
		$schemas = Schema_DB::get_schemas( $post_id );
		if ( ! empty( $schemas ) || metadata_exists( 'post', $post_id, 'rank_math_rich_snippet' ) ) {
			return $schemas;
		}

		$post_type    = get_post_type( $post_id );
		$default_type = ucfirst( Helper::get_default_schema_type( $post_id ) );
		if ( ! $default_type ) {
			return [];
		}

		$schemas['new-9999'] = [
			'@type'    => $default_type,
			'metadata' => [
				'title'     => Helper::sanitize_schema_title( $default_type ),
				'type'      => 'template',
				'shortcode' => uniqid( 's-' ),
				'isPrimary' => true,
			],
		];

		return $schemas;
	}
}
