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
use RankMath\Admin\Admin_Helper;
use RankMath\Helpers\Param;
use RankMath\Helpers\Security;
use RankMath\Google\Authentication;

defined( 'ABSPATH' ) || exit;

/**
 * Registration class.
 */
class Registration {

	use Hooker;

	/**
	 * Page slug.
	 *
	 * @var string
	 */
	private $slug = 'rank-math-registration';

	/**
	 * Hold current step.
	 *
	 * @var string
	 */
	protected $step = '';

	/**
	 * Current step slug.
	 *
	 * @var string
	 */
	protected $step_slug = '';

	/**
	 * Is registration invalid.
	 *
	 * @var bool
	 */
	public $invalid = false;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->step      = 'register';
		$this->step_slug = 'register';
		$this->invalid   = Helper::is_invalid_registration();

		if ( $this->invalid ) {
			$this->action( 'admin_menu', 'admin_menu' );
			$this->action( 'admin_init', 'redirect_to_welcome' );
			$this->action( 'admin_post_rank_math_save_registration', 'save_registration' );
			$this->action( 'admin_post_rank_math_skip_wizard', 'skip_wizard' );
			$this->action( 'admin_init', 'render_page', 30 );
		}

		$this->action( 'admin_init', 'handle_registration' );
		$this->filter( 'allowed_redirect_hosts', 'allowed_redirect_hosts' );
	}

	/**
	 * Add allowed redirect hosts.
	 *
	 * @param  array $hosts Allowed hosts.
	 * @return array
	 */
	public function allowed_redirect_hosts( $hosts ) {
		$hosts[] = 'rankmath.com';
		return $hosts;
	}

	/**
	 * Check for activation.
	 */
	public function handle_registration() {

		// Bail if already connected.
		if ( Helper::is_site_connected() ) {
			return;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return;
		}

		$nonce = Param::get( 'nonce' );
		if ( ! $nonce || ! wp_verify_nonce( $nonce, 'rank_math_register_product' ) ) {
			return;
		}

		$status = Param::get( 'rankmath_connect' );
		if ( $status && $redirect_to = $this->get_registration_url( $status ) ) { //phpcs:ignore
			Helper::redirect( $redirect_to );
			exit;
		}
	}

	/**
	 * Handle activation.
	 *
	 * @param  string $status Status parameter.
	 */
	private function get_registration_url( $status ) {
		if ( 'cancel' === $status ) {
			// User canceled activation.
			Helper::add_notification( __( 'Rank Math plugin could not be connected.', 'rank-math' ), [ 'type' => 'error' ] );
			return Security::remove_query_arg_raw( [ 'rankmath_connect', 'rankmath_auth' ] );
		}

		if ( 'banned' === $status ) {
			// User or site banned.
			Helper::add_notification( __( 'Unable to connect Rank Math.', 'rank-math' ), [ 'type' => 'error' ] );
			return Security::remove_query_arg_raw( [ 'rankmath_connect', 'rankmath_auth' ] );
		}

		if ( 'ok' === $status && $auth_data = $this->get_registration_params() ) { // phpcs:ignore
			Admin_Helper::get_registration_data(
				[
					'username'  => $auth_data['username'],
					'email'     => $auth_data['email'],
					'api_key'   => $auth_data['api_key'],
					'plan'      => $auth_data['plan'],
					'connected' => true,
					'site_url'  => Helper::get_home_url(),
				]
			);

			if ( 1 === absint( Param::get( 'analytics' ) ) ) {
				wp_redirect( Authentication::get_auth_url() ); //phpcs:ignore -- This is used to redirect to the external url.
				exit;
			}

			// Redirect to the wizard is registration successful.
			if ( Param::get( 'page' ) === 'rank-math-registration' ) {
				return Helper::get_admin_url( 'wizard' );
			}

			return Security::remove_query_arg_raw( [ 'rankmath_connect', 'rankmath_auth', 'nonce' ] );
		}

		return false;
	}

	/**
	 * Check if 'rankmath_auth' contains all the data we need, in the
	 * correct format.
	 *
	 * @return bool|array Whether the input is valid.
	 */
	private function get_registration_params() {
		$params = Param::get( 'rankmath_auth' );
		if ( false === $params ) {
			return false;
		}

		$params = json_decode( base64_decode( $params ), true ); // phpcs:ignore -- Verified as safe usage.
		if (
			! is_array( $params ) ||
			! isset( $params['username'] ) ||
			! isset( $params['email'] ) ||
			! isset( $params['api_key'] )
		) {
			return false;
		}

		return $params;
	}

	/**
	 * Redirect to welcome page.
	 *
	 * Redirect the user to the welcome page after plugin activation.
	 */
	public function redirect_to_welcome() {
		if ( ! $this->can_redirect() ) {
			return;
		}

		$url = '';
		if ( $this->invalid ) {
			$url = 'registration';
		} elseif ( ! get_option( 'rank_math_wizard_completed' ) ) {
			$url = 'wizard';
		}

		Helper::redirect( Helper::get_admin_url( $url ) );
		exit;
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		add_menu_page(
			esc_html__( 'Rank Math', 'rank-math' ),
			esc_html__( 'Rank Math SEO', 'rank-math' ),
			'manage_options',
			$this->slug,
			[ $this, 'render_page' ]
		);
	}

	/**
	 * Output the admin page.
	 */
	public function render_page() {

		// Early bail if we're not on the right page.
		if ( Param::get( 'page' ) !== $this->slug ) {
			return;
		}

		if ( ob_get_length() ) {
			ob_end_clean();
		}

		$assets = new Assets();
		$assets->register();

		wp_styles()->done  = [];
		wp_scripts()->done = [];

		// Wizard.
		wp_enqueue_media();
		wp_enqueue_style( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/css/setup-wizard.css', [ 'wp-admin', 'buttons', 'wp-components', 'rank-math-common', 'rank-math-cmb2' ], rank_math()->version );
		wp_enqueue_script( 'rank-math-registration', rank_math()->plugin_url() . 'assets/admin/js/registration.js', [ 'lodash', 'react', 'react-dom', 'wp-element', 'wp-data', 'rank-math-components' ], rank_math()->version, true );
		Helper::add_json( 'logo', esc_url( rank_math()->plugin_url() . 'assets/admin/img/logo.svg' ) );
		Helper::add_json( 'registerNonce', wp_create_nonce( 'rank-math-wizard' ) );
		Helper::add_json( 'adminUrl', esc_url( admin_url( 'admin-post.php' ) ) );
		Helper::add_json( 'isSiteUrlValid', Admin_Helper::is_site_url_valid() );
		Helper::add_json( 'optionsPage', esc_url( admin_url( 'options-general.php' ) ) );

		ob_start();

		/**
		 * Start the actual page content.
		 */
		include_once rank_math()->admin_dir() . 'wizard/views/content.php';
		exit;
	}

	/**
	 * Execute save handler for current step.
	 */
	public function save_registration() {
		// If no form submission, bail.
		$referer = Param::post( '_wp_http_referer', get_dashboard_url() );
		if ( Param::post( 'step' ) !== 'register' ) {
			return Helper::redirect( $referer );
		}

		check_admin_referer( 'rank-math-wizard', 'security' );
		if ( ! Helper::has_cap( 'general' ) ) {
			return Helper::redirect( $referer );
		}

		$this->redirect_to_connect( $_POST );
	}

	/**
	 * Skip wizard handler.
	 */
	public function skip_wizard() {
		check_admin_referer( 'rank-math-wizard', 'security' );
		if ( ! Helper::has_cap( 'general' ) ) {
			exit;
		}
		add_option( 'rank_math_registration_skip', true );
		Helper::redirect( Helper::get_admin_url( 'wizard' ) );
		exit;
	}

	/**
	 * Authenticate registration.
	 *
	 * @param array $values Array of values for the step to process.
	 */
	private function redirect_to_connect( $values ) {
		if ( ! isset( $values['rank_math_activate'] ) ) {
			Admin_Helper::deregister_user();
			return;
		}

		$url = Admin_Helper::get_activate_url( Helper::get_admin_url( 'registration' ) );
		wp_safe_redirect( $url );
		die();
	}

	/**
	 * Can redirect to setup/registration page after install.
	 *
	 * @return bool
	 */
	private function can_redirect() {
		if ( ! get_transient( '_rank_math_activation_redirect' ) ) {
			return false;
		}

		delete_transient( '_rank_math_activation_redirect' );

		if ( ( ! empty( $_GET['page'] ) && in_array( $_GET['page'], [ 'rank-math-registration', 'rank-math-wizard' ], true ) ) || ! current_user_can( 'manage_options' ) ) {
			return false;
		}

		return true;
	}
}
