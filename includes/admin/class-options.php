<?php
/**
 * The option page functionality of the plugin.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use WP_Http;
use RankMath\KB;
use RankMath\CMB2;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;
use RankMath\Helpers\Param;
use RankMath\Robots_Txt;
use RankMath\Sitemap\Router;
use RankMath\Sitemap\Sitemap;
use RankMath\Admin\Page;

defined( 'ABSPATH' ) || exit;

/**
 * Options class.
 */
class Options {

	use Hooker;

	/**
	 * Page title.
	 *
	 * @var string
	 */
	public $title = 'Settings';

	/**
	 * Menu title.
	 *
	 * @var string
	 */
	public $menu_title = 'Settings';

	/**
	 * Hold tabs for page.
	 *
	 * @var array
	 */
	public $tabs = [];

	/**
	 * Hold folder name for tab files.
	 *
	 * @var string
	 */
	public $folder = '';

	/**
	 * Menu Position.
	 *
	 * @var int
	 */
	public $position = 10;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	public $capability = 'manage_options';

	/**
	 * CMB2 option page id.
	 *
	 * @var string
	 */
	private $cmb_id = null;

	/**
	 * Options key.
	 *
	 * @var string
	 */
	public $key = '';

	/**
	 * The Constructor
	 *
	 * @param array $config Array of configuration.
	 */
	public function __construct( $config ) {
		$this->config( $config );
		$this->cmb_id = $this->key . '_options';

		$this->action( 'admin_post_' . $this->key, 'reset_options', 2 );
	}

	/**
	 * Create option object and add settings.
	 */
	public function register_option_page() {
		$current_page = str_replace( 'rank-math-options-', '', $this->key );

		new Page(
			$this->key,
			$this->title,
			[
				'position'   => $this->position,
				'priority'   => 9999,
				'parent'     => 'rank-math',
				'capability' => $this->capability,
				'menu_title' => $this->menu_title,
				'render'     => [ $this, 'display' ],
				'classes'    => $this->get_body_class(),
				'assets'     => [
					'styles'  => [
						'select2-rm'        => '',
						'rank-math-common'  => '',
						'rank-math-cmb2'    => '',
						'wp-components'     => '',
						'rank-math-options' => rank_math()->plugin_url() . 'assets/admin/css/option-panel.css',
					],
					'scripts' => [
						'media-editor'         => '',
						'underscore'           => '',
						'select2-rm'           => '',
						'lodash'               => '',
						'rank-math-common'     => '',
						'wp-api-fetch'         => '',
						'wp-data'              => '',
						'rank-math-components' => '',
						'rank-math-options'    => rank_math()->plugin_url() . 'assets/admin/js/settings.js',
					],
					'json'    => $this->get_json_data( $current_page ),
				],
			]
		);
	}

	/**
	 * Set the default values if not set.
	 *
	 * @param CMB2 $cmb The CMB2 object to hookup.
	 */
	public function set_defaults( $cmb ) {
		foreach ( $cmb->prop( 'fields' ) as $id => $field_args ) {
			$field = $cmb->get_field( $id );
			if ( isset( $field_args['default'] ) || isset( $field_args['default_cb'] ) ) {
				$defaults[ $id ] = $field->get_default();
			}
		}

		// Save Defaults if any.
		if ( ! empty( $defaults ) ) {
			add_option( $this->key, $defaults );
		}
	}

	/**
	 * Reset options.
	 */
	public function reset_options() {

		if ( ! check_admin_referer( 'rank-math-reset-options' ) || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		$url = wp_get_referer();
		if ( ! $url ) {
			$url = admin_url();
		}

		if ( filter_has_var( INPUT_POST, 'reset-cmb' ) && Param::post( 'action' ) === $this->key ) {
			delete_option( $this->key );
			Helper::redirect( esc_url_raw( $url ), WP_Http::SEE_OTHER );
			exit;
		}
	}

	/**
	 * Add classes to <body> of WordPress admin.
	 *
	 * @return string
	 */
	public function get_body_class() {
		$mode = Helper::is_advanced_mode() ? 'advanced' : 'basic';
		return [
			'rank-math-page ',
			'rank-math-mode-' . $mode,
		];
	}

	/**
	 * Display Setting on a page.
	 */
	public function display() {
		?>
			<div id="rank-math-options" class="<?php echo esc_attr( $this->cmb_id ); ?>"></div>
		<?php
	}

	/**
	 * Get setting tabs.
	 *
	 * @return array
	 */
	private function get_tabs() {

		$filter = str_replace( '-', '_', str_replace( 'rank-math-', '', $this->key ) );
		/**
		 * Allow developers to add new tabs into option panel.
		 *
		 * The dynamic part of hook is, page name without 'rank-math-' prefix.
		 *
		 * @param array $tabs
		 */
		return $this->do_filter( "admin/options/{$filter}_tabs", $this->tabs );
	}

	/**
	 * Get localized data for the current settings page.
	 *
	 * @param string $current_page Current Settings page.
	 *
	 * @return array
	 */
	private function get_json_data( $current_page ) {
		if ( is_admin() ) {
			rank_math()->variables->setup();
			rank_math()->variables->setup_json();
		}

		$tabs = $this->get_tabs();
		$data = $this->do_filter(
			"admin/options/{$current_page}_data",
			[
				'isPro'           => defined( 'RANK_MATH_PRO_FILE' ),
				'tabs'            => array_keys( $tabs ),
				'optionPage'      => $current_page,
				'homeUrl'         => get_home_url(),
				'data'            => $current_page === 'instant-indexing' ? get_option( 'rank-math-options-instant-indexing' ) : Helper::get_settings( $current_page ),
				'isSiteConnected' => Helper::is_site_connected(),
				'choices'         => [
					'postTypes'            => Helper::choices_post_types(),
					'accessiblePostTypes'  => Helper::get_accessible_post_types(),
					'accessibleTaxonomies' => Helper::get_accessible_taxonomies(),
					'choicesPostTypeIcons' => Helper::choices_post_type_icons(),
					'choicesTaxonomyIcons' => Helper::choices_taxonomy_icons(),
				],
			]
		);
		foreach ( $tabs as $tab ) {
			if ( empty( $tab['json'] ) ) {
				continue;
			}

			$data = array_merge( $data, $tab['json'] );
		}

		$method = "get_{$current_page}_data";
		if ( ! method_exists( $this, $method ) ) {
			return $data;
		}

		return array_merge( $data, $this->$method() );
	}

	/**
	 * Get General Settings page data.
	 *
	 * @return array
	 */
	private function get_general_data() {
		return [
			'activateUrl'          => Admin_Helper::get_activate_url( admin_url( 'admin.php??page=rank-math-options-general&tab=content-ai' ) ),
			'hasBreadcrumbSupport' => current_theme_supports( 'rank-math-breadcrumbs' ),
			'showBlogPage'         => 'page' === get_option( 'show_on_front' ) && get_option( 'page_for_posts' ) > 0,
			'isEditAllowed'        => Helper::is_edit_allowed(),
			'defaultLanguage'      => Helper::content_ai_default_language(),
		];
	}

	/**
	 * Get General Settings page data.
	 *
	 * @return array
	 */
	private function get_titles_data() {
		$data = [
			'choicesRobots'         => Helper::choices_robots(),
			'supportsTitleTag'      => current_theme_supports( 'title-tag' ) || wp_is_block_theme(),
			'schemaTypes'           => Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'rank-math' ) ),
			'isRedirectAttachments' => Helper::get_settings( 'general.attachment_redirect_urls' ),
		];
		return $data;
	}
}
