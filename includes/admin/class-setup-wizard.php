<?php
/**
 * The Setup Wizard - configure the SEO settings in just a few steps.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Traits\Wizard;
use RankMath\Admin\Importers\Detector;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Setup_Wizard class.
 */
class Setup_Wizard {

	use Hooker;
	use Wizard;

	/**
	 * Top level admin page.
	 *
	 * @var string
	 */
	protected $slug = 'rank-math-wizard';

	/**
	 * Hook suffix.
	 *
	 * @var string
	 */
	public $hook_suffix = '';

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'admin_menu', 'add_admin_menu' );

		// If the page is not this page stop here.
		if ( ! $this->is_current_page() ) {
			return;
		}

		$this->action( 'admin_init', 'admin_page', 30 );
		$this->filter( 'user_has_cap', 'filter_user_has_cap' );
	}

	/**
	 * Add the admin menu item, under Appearance.
	 */
	public function add_admin_menu() {
		if ( Param::get( 'page' ) !== $this->slug ) {
			return;
		}

		$this->hook_suffix = add_submenu_page(
			'',
			esc_html__( 'Setup Wizard', 'rank-math' ),
			esc_html__( 'Setup Wizard', 'rank-math' ),
			'manage_options',
			$this->slug,
			[ $this, 'admin_page' ]
		);
	}

	/**
	 * Output the admin page.
	 */
	public function admin_page() {

		// Do not proceed if we're not on the right page.
		if ( Param::get( 'page' ) !== $this->slug ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		// Enqueue styles.
		rank_math()->admin_assets->register();
		wp_enqueue_style( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/css/setup-wizard.css', [ 'wp-admin', 'buttons', 'select2-rm', 'rank-math-common', 'rank-math-cmb2', 'wp-components' ], rank_math()->version );

		// Enqueue scripts for the SEO Score Updater tool.
		\RankMath\Tools\Update_Score::get()->enqueue();

		// Enqueue javascript.
		wp_enqueue_media();
		wp_enqueue_script( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/js/wizard.js', [ 'media-editor', 'select2-rm', 'lodash', 'rank-math-common', 'rank-math-components' ], rank_math()->version, true );
		wp_set_script_translations( 'rank-math-wizard', 'rank-math' );

		Helper::add_json( 'logo', esc_url( rank_math()->plugin_url() . 'assets/admin/img/logo.svg' ) );

		ob_start();

		/**
		 * Start the actual page content.
		 */
		include_once $this->get_view( 'content' );
		exit;
	}

	/**
	 * Get view file to display.
	 *
	 * @param string $view View to display.
	 * @return string
	 */
	public function get_view( $view ) {
		return rank_math()->admin_dir() . "wizard/views/{$view}.php";
	}

	/**
	 * Get Localized data for the given step.
	 *
	 * @param string $step Current Setup Wizard step.
	 */
	public static function get_localized_data( $step ) {
		$steps = self::get_steps();
		if ( ! isset( $steps[ $step ] ) ) {
			return '';
		}

		$data = [
			'isWhitelabel' => Helper::is_whitelabel(),
			'isConfigured' => Helper::is_configured(),
			'setup_mode'   => Helper::get_settings( 'general.setup_mode', 'advanced' ),
			'addImport'    => ! self::maybe_remove_import(),
		];

		return apply_filters(
			"rank_math/setup_wizard/$step/localized_data",
			array_merge(
				$data,
				$steps[ $step ]::get_localized_data()
			)
		);
	}

	/**
	 * Get Localized data for the given step.
	 *
	 * @param string $step   Current Setup Wizard step.
	 * @param array  $values Values to update.
	 */
	public static function save_data( $step, $values ) {
		$steps = self::get_steps();
		if ( ! isset( $steps[ $step ] ) ) {
			return '';
		}

		do_action( "rank_math/setup_wizard/$step/save_data", $values );

		return $steps[ $step ]::save( $values );
	}

	/**
	 * Get Setup Wizard step class.
	 */
	private static function get_steps() {
		return [
			'compatibility' => '\\RankMath\\Wizard\\Compatibility',
			'import'        => '\\RankMath\\Wizard\\Import',
			'yoursite'      => '\\RankMath\\Wizard\\Your_Site',
			'analytics'     => '\\RankMath\\Wizard\\Search_Console',
			'sitemaps'      => '\\RankMath\\Wizard\\Sitemap',
			'optimization'  => '\\RankMath\\Wizard\\Optimization',
			'ready'         => '\\RankMath\\Wizard\\Ready',
			'role'          => '\\RankMath\\Wizard\\Role',
			'redirection'   => '\\RankMath\\Wizard\\Monitor_Redirection',
			'schema-markup' => '\\RankMath\\Wizard\\Schema_Markup',
		];
	}

	/**
	 * Maybe remove import step.
	 *
	 * @return bool
	 */
	private static function maybe_remove_import() {
		$pre = apply_filters( 'rank_math/wizard/pre_remove_import_step', null );
		if ( ! is_null( $pre ) ) {
			return $pre;
		}

		if ( false === get_option( 'rank_math_is_configured' ) ) {
			$detector = new Detector();
			$plugins  = $detector->detect();
			if ( ! empty( $plugins ) ) {
				return false;
			}
		}

		return true;
	}
}
