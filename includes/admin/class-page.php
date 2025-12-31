<?php
/**
 * The admin-page functionality.
 *
 * @since      1.0.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     RankMath <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helpers\Param;
use RankMath\Helper;

/**
 * Page class.
 */
class Page {

	/**
	 * Unique ID used for menu_slug.
	 *
	 * @var string
	 */
	public $id = null;

	/**
	 * The text to be displayed in the title tags of the page.
	 *
	 * @var string
	 */
	public $title = null;

	/**
	 * The slug name for the parent menu.
	 *
	 * @var string
	 */
	public $parent = null;

	/**
	 * The The on-screen name text for the menu.
	 *
	 * @var string
	 */
	public $menu_title = null;

	/**
	 * The capability required for this menu to be displayed to the user.
	 *
	 * @var string
	 */
	public $capability = 'manage_options';

	/**
	 * The icon for this menu.
	 *
	 * @var string
	 */
	public $icon = 'dashicons-art';

	/**
	 * The position in the menu order this menu should appear.
	 *
	 * @var int
	 */
	public $position = -1;

	/**
	 * The init hook priority.
	 *
	 * @var int
	 */
	public $priority = 25;

	/**
	 * The function/file that displays the page content for the menu page.
	 *
	 * @var string|callable
	 */
	public $render = null;

	/**
	 * The function that run on page POST to save data.
	 *
	 * @var callable
	 */
	public $onsave = null;

	/**
	 * Hold contextual help tabs.
	 *
	 * @var array
	 */
	public $help = null;

	/**
	 * Hold scripts and styles.
	 *
	 * @var array
	 */
	public $assets = null;

	/**
	 * Check if plugin is network active.
	 *
	 * @var array
	 */
	public $is_network = false;

	/**
	 * Hold classes for body tag.
	 *
	 * @var array
	 */
	public $classes = null;

	/**
	 * Hold localized data.
	 *
	 * @var array
	 */
	public $json = null;

	/**
	 * The Constructor.
	 *
	 * @param string $id     Admin page unique id.
	 * @param string $title  Title of the admin page.
	 * @param array  $config Optional. Override page settings.
	 */
	public function __construct( $id, $title, $config = [] ) {

		// Early bail!
		if ( ! $id ) {
			wp_die( esc_html__( '$id variable required', 'rank-math' ), esc_html__( 'Variable Required', 'rank-math' ) );
		}

		if ( ! $title ) {
			wp_die( esc_html__( '$title variable required', 'rank-math' ), esc_html__( 'Variable Required', 'rank-math' ) );
		}

		$this->id    = $id;
		$this->title = $title;
		foreach ( $config as $key => $value ) {
			$this->$key = $value;
		}

		if ( ! $this->menu_title ) {
			$this->menu_title = $title;
		}

		add_action( 'init', [ $this, 'init' ], $this->priority ?? 25 );
	}

	/**
	 * Init admin page when WordPress Initialises.
	 *
	 * @codeCoverageIgnore
	 */
	public function init() {
		$priority = $this->parent ? intval( $this->position ) : -1;
		add_action( $this->is_network ? 'network_admin_menu' : 'admin_menu', [ $this, 'register_menu' ], $priority );

		// If not the page is not this page stop here.
		if ( ! $this->is_current_page() ) {
			return;
		}

		$hooks = [
			'admin_init'            => [
				'callback'  => 'save',
				'condition' => ! is_null( $this->onsave ) && is_callable( $this->onsave ),
			],
			'admin_enqueue_scripts' => [
				'callback'  => 'enqueue',
				'condition' => ! empty( $this->assets ),
			],
			'admin_head'            => [
				'callback'  => 'contextual_help',
				'condition' => ! empty( $this->help ),
			],
			'admin_body_class'      => [
				'callback'  => 'body_class',
				'condition' => ! empty( $this->classes ),
			],
		];

		foreach ( $hooks as $hook => $data ) {
			if ( true === $data['condition'] ) {
				add_action( $hook, [ $this, $data['callback'] ] );
			}
		}
	}

	/**
	 * Register Admin Menu.
	 *
	 * @codeCoverageIgnore
	 */
	public function register_menu() {
		if ( ! $this->parent ) {
			add_menu_page( $this->title, $this->menu_title, $this->capability, $this->id, [ $this, 'display' ], $this->icon, $this->position );
			return;
		}

		add_submenu_page( $this->parent, $this->title, $this->menu_title, $this->capability, $this->id, [ $this, 'display' ] );
	}

	/**
	 * Enqueue styles and scripts.
	 *
	 * @codeCoverageIgnore
	 */
	public function enqueue() {
		$this->enqueue_styles();
		$this->enqueue_scripts();
		$this->add_localized_data();
	}

	/**
	 * Add classes to <body> of WordPress admin.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $classes Space-separated list of CSS classes.
	 *
	 * @return string
	 */
	public function body_class( $classes = '' ) {
		return $classes . ' ' . join( ' ', $this->classes );
	}

	/**
	 * Save anything you want using onsave function.
	 *
	 * @codeCoverageIgnore
	 */
	public function save() {
		call_user_func( $this->onsave, $this );
	}

	/**
	 * Contextual Help.
	 *
	 * @codeCoverageIgnore
	 */
	public function contextual_help() {
		$screen = get_current_screen();

		foreach ( $this->help as $tab_id => $tab ) {
			$tab['id']      = $tab_id;
			$tab['content'] = $this->get_help_content( $tab );
			$screen->add_help_tab( $tab );
		}
	}

	/**
	 * Render admin page content using render function you passed in config.
	 *
	 * @codeCoverageIgnore
	 */
	public function display() {
		if ( is_null( $this->render ) ) {
			return;
		}

		if ( 'settings' === $this->render ) {
			return $this->display_settings();
		}

		if ( is_callable( $this->render ) ) {
			call_user_func( $this->render, $this );
			return;
		}

		if ( is_string( $this->render ) ) {
			include_once $this->render;
		}
	}

	/**
	 * Is the page is current page.
	 *
	 * @return bool
	 */
	public function is_current_page() {
		return Param::get( 'page' ) === $this->id;
	}

	/**
	 * Enqueue styles
	 *
	 * @codeCoverageIgnore
	 */
	private function enqueue_styles() {
		if ( ! isset( $this->assets['styles'] ) || empty( $this->assets['styles'] ) ) {
			return;
		}

		foreach ( $this->assets['styles'] as $handle => $src ) {
			wp_enqueue_style( $handle, $src, null, rank_math()->version );
		}
	}

	/**
	 * Enqueue scripts.
	 *
	 * @codeCoverageIgnore
	 */
	private function enqueue_scripts() {
		if ( ! isset( $this->assets['scripts'] ) || empty( $this->assets['scripts'] ) ) {
			return;
		}

		foreach ( $this->assets['scripts'] as $handle => $src ) {
			if ( $handle === 'media-editor' ) {
				wp_enqueue_media();
			}
			wp_enqueue_script( $handle, $src, null, rank_math()->version, true );
		}

		do_action( 'rank-math/admin_enqueue_scripts' );
	}

	/**
	 * Get tab content
	 *
	 * @codeCoverageIgnore
	 *
	 * @param array $tab Tab to get content for.
	 *
	 * @return string
	 */
	private function get_help_content( $tab ) {
		ob_start();

		// If it is a function.
		if ( isset( $tab['content'] ) && is_callable( $tab['content'] ) ) {
			call_user_func( $tab['content'] );
		}

		// If it is a file.
		if ( isset( $tab['view'] ) && $tab['view'] ) {
			require $tab['view'];
		}

		return ob_get_clean();
	}

	/**
	 * Localized data.
	 */
	private function add_localized_data() {
		if ( empty( $this->assets['json'] ) ) {
			return;
		}

		foreach ( $this->assets['json'] as $key => $value ) {
			Helper::add_json( $key, $value );
		}

		Helper::add_json(
			'settings',
			[
				'general' => Helper::get_settings( 'general' ),
				'titles'  => Helper::get_settings( 'titles' ),
				'sitemap' => Helper::get_settings( 'sitemap' ),
			]
		);
	}

	/**
	 * Display settings.
	 */
	private function display_settings() {
		echo '<div id="rank-math-settings" class="' . esc_attr( $this->id ) . '"></div>';
	}
}
