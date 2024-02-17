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

		$this->action( 'cmb2_admin_init', 'register_option_page', $this->position );
		$this->action( 'admin_post_' . $this->key, 'reset_options', 2 );

		if ( true === empty( get_option( $this->key ) ) ) {
			$this->action( 'cmb2_init_hookup_' . $this->cmb_id, 'set_defaults', 11 );
		}

		if ( ! $this->is_current_page() ) {
			return;
		}

		$this->action( 'admin_enqueue_scripts', 'enqueue' );
		$this->action( 'admin_body_class', 'body_class' );
	}

	/**
	 * Create cmb2 box.
	 *
	 * @return CMB2
	 */
	private function create_cmb2() {
		return new_cmb2_box(
			[
				'id'           => $this->cmb_id,
				'title'        => $this->title,
				'menu_title'   => $this->menu_title,
				'capability'   => $this->capability,
				'object_types' => [ 'options-page' ],
				'option_key'   => $this->key,
				'parent_slug'  => 'rank-math',
				'cmb_styles'   => false,
				'display_cb'   => [ $this, 'display' ],
			]
		);
	}

	/**
	 * Create option object and add settings.
	 */
	public function register_option_page() {
		$cmb  = $this->create_cmb2();
		$tabs = $this->get_tabs();

		$cmb->add_field(
			[
				'id'      => 'setting-panel-container-' . $this->cmb_id,
				'type'    => 'tab_container_open',
				'classes' => 'before-header',
				'tabs'    => $tabs,
			]
		);

		foreach ( $tabs as $id => $tab ) {
			$located = $this->locate_file( $id, $tab );
			if ( false === $located ) {
				continue;
			}

			$cmb->add_field(
				[
					'name'    => esc_html__( 'Panel', 'rank-math' ),
					'id'      => 'setting-panel-' . $id,
					'type'    => 'tab',
					'open'    => true,
					'classes' => isset( $tab['classes'] ) ? $tab['classes'] : '',
				]
			);

			$cmb->add_field(
				[
					'id'        => $id . '_section_title',
					'type'      => 'title',
					'name'      => isset( $tab['page_title'] ) ? $tab['page_title'] : ( isset( $tab['title'] ) ? $tab['title'] : '' ),
					'desc'      => isset( $tab['desc'] ) ? $tab['desc'] : '',
					'after'     => isset( $tab['after'] ) ? $tab['after'] : '',
					'classes'   => 'tab-header',
					'after_row' => isset( $tab['after_row'] ) ? $tab['after_row'] : '',
				]
			);

			include $located;

			$this->do_action( "admin/settings/{$id}", $cmb, $tab );

			$cmb->add_field(
				[
					'id'   => 'setting-panel-' . $id . '-close',
					'type' => 'tab',
				]
			);
		}

		$cmb->add_field(
			[
				'id'   => 'setting-panel-container-close-' . $this->cmb_id,
				'type' => 'tab_container_close',
			]
		);

		CMB2::pre_init( $cmb );
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
	 * Enqueue styles and scripts.
	 */
	public function enqueue() {
		$screen = get_current_screen();

		if ( ! Str::contains( $this->key, $screen->id ) ) {
			return;
		}

		\CMB2_Hookup::enqueue_cmb_css();
		rank_math()->variables->setup_json();
		wp_enqueue_style( 'rank-math-options', rank_math()->plugin_url() . 'assets/admin/css/option-panel.css', [ 'select2-rm', 'rank-math-common', 'rank-math-cmb2' ], rank_math()->version );
		wp_enqueue_script( 'rank-math-options', rank_math()->plugin_url() . 'assets/admin/js/option-panel.js', [ 'underscore', 'select2-rm', 'lodash', 'rank-math-common', 'wp-api-fetch' ], rank_math()->version, true );

		// Add thank you.
		Helper::add_json( 'indexUrl', rank_math()->plugin_url() . 'assets/admin/js/search-index/' );
		Helper::add_json( 'optionPage', str_replace( 'rank-math-options-', '', $this->key ) );
	}

	/**
	 * Add classes to <body> of WordPress admin.
	 *
	 * @param string $classes List of CSS classes.
	 * @return string
	 */
	public function body_class( $classes = '' ) {
		$mode = Helper::is_advanced_mode() ? 'advanced' : 'basic';
		return $classes . ' rank-math-page rank-math-mode-' . $mode;
	}

	/**
	 * Display Setting on a page.
	 *
	 * @param CMB2_Options $machine Current CMB2 box object.
	 */
	public function display( $machine ) {
		$cmb = $machine->cmb;

		// Header.
		rank_math()->admin->display_admin_header();
		?>

		<?php if ( ! defined( 'RANK_MATH_PRO_FILE' ) ) : ?>
			<div class="rank-math-unlock-pro-notice" id="rank-math-unlock-pro-notice">
				<a href="<?php KB::the( 'pro', 'Unlock PRO Options Panel Notice' ); ?>" target="_blank" class="pro-link">
					<p>
						<?php esc_html_e( 'Take your SEO to the Next Level!', 'rank-math' ); ?>
						<strong><?php esc_html_e( 'Get Rank Math PRO!', 'rank-math' ); ?></strong>
						<span><?php esc_html_e( 'Click here to see all the exciting features.', 'rank-math' ); ?></span>
					</p>
					<div class="close-notice">
						<span class="dashicons dashicons-dismiss"></span>
					</div>
				</a>
			</div>
		<?php endif; ?>

		<div class="wrap rank-math-wrap rank-math-wrap-settings">

			<span class="wp-header-end"></span>

			<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" id="<?php echo esc_attr( $cmb->cmb_id ); ?>" enctype="multipart/form-data" encoding="multipart/form-data">

				<input type="hidden" name="action" value="<?php echo esc_attr( $machine->option_key ); ?>">
				<?php $machine->options_page_metabox(); ?>

				<footer class="form-footer rank-math-ui settings-footer wp-clearfix">
					<?php wp_nonce_field( 'rank-math-reset-options' ); ?>
					<input type="submit" name="submit-cmb" id="submit-cmb" title="<?php echo esc_html__( 'Ctrl/Cmd + Enter', 'rank-math' ); ?>" class="button button-primary save-options" value="<?php esc_attr_e( 'Save Changes', 'rank-math' ); ?>">
					<input type="submit" name="reset-cmb" id="rank-math-reset-cmb" value="<?php esc_attr_e( 'Reset Options', 'rank-math' ); ?>" class="button button-secondary reset-options alignleft">
				</footer>

			</form>

		</div>

		<?php
	}

	/**
	 * Check if we are on the correct page.
	 *
	 * @return bool
	 */
	public function is_current_page() {
		return Param::request( 'page' ) === $this->key || Param::request( 'action' ) === $this->key;
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
	 * Locate tab options file.
	 *
	 * @param  string $id  Tab id.
	 * @param  array  $tab Tab options.
	 * @return string|boolean
	 */
	private function locate_file( $id, $tab ) {
		if ( isset( $tab['type'] ) && 'seprator' === $tab['type'] ) {
			return false;
		}

		$file = isset( $tab['file'] ) && ! empty( $tab['file'] ) ? $tab['file'] : rank_math()->includes_dir() . "settings/{$this->folder}/{$id}.php";

		return file_exists( $file ) ? $file : false;
	}
}
