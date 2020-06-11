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

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\Param;
use RankMath\Helpers\Security;

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
	 * The text string array.
	 *
	 * @var array
	 */
	protected $strings = null;

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
		// Strings passed in from the config file.
		$this->strings = [
			'title'               => esc_html__( 'Rank Math Product Registration', 'rank-math' ),
			'return-to-dashboard' => esc_html__( 'Return to dashboard', 'rank-math' ),
		];

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
			\wp_safe_redirect( $redirect_to );
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
			return Security::remove_query_arg_raw( array( 'rankmath_connect', 'rankmath_auth' ) );
		}

		if ( 'banned' === $status ) {
			// User or site banned.
			Helper::add_notification( __( 'Unable to connect Rank Math.', 'rank-math' ), [ 'type' => 'error' ] );
			return Security::remove_query_arg_raw( array( 'rankmath_connect', 'rankmath_auth' ) );
		}

		if ( 'ok' === $status && $auth_data = $this->get_registration_params() ) { // phpcs:ignore
			Admin_Helper::get_registration_data(
				[
					'username'  => $auth_data['username'],
					'email'     => $auth_data['email'],
					'api_key'   => $auth_data['api_key'],
					'connected' => true,
				]
			);

			// Redirect to the wizard is registration successful.
			if ( Param::get( 'page' ) === 'rank-math-registration' ) {
				return Helper::get_admin_url( 'wizard' );
			}

			return Security::remove_query_arg_raw( array( 'rankmath_connect', 'rankmath_auth', 'nonce' ) );
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

		$params = json_decode( base64_decode( $params ), true );
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

		wp_redirect( Helper::get_admin_url( $url ) );
		exit;
	}

	/**
	 * Add menu items.
	 */
	public function admin_menu() {
		add_menu_page(
			esc_html__( 'Rank Math', 'rank-math' ),
			esc_html__( 'Rank Math', 'rank-math' ),
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

		$assets = new Assets;
		$assets->register();

		wp_styles()->done  = [];
		wp_scripts()->done = [];

		// Enqueue styles.
		\CMB2_Hookup::enqueue_cmb_css();
		\CMB2_Hookup::enqueue_cmb_js();

		// Wizard.
		wp_enqueue_style( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/css/setup-wizard.css', [ 'wp-admin', 'buttons', 'cmb2-styles', 'rank-math-common', 'rank-math-cmb2' ], rank_math()->version );
		wp_enqueue_script( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/js/wizard.js', [ 'jquery', 'rank-math-common', 'rank-math-validate' ], rank_math()->version, true );
		wp_localize_script( 'rank-math-wizard', 'wp', [] );

		$logo_url = '<a href="' . KB::get( 'logo' ) . '" target="_blank"><img src="' . esc_url( rank_math()->plugin_url() . 'assets/admin/img/logo.svg' ) . '"></a>';

		ob_start();

		/**
		 * Start the actual page content.
		 */
		include_once $this->get_view( 'header' );
		include_once $this->get_view( 'content' );
		include_once $this->get_view( 'footer' );
		exit;
	}

	/**
	 * Render page body.
	 */
	protected function body() {
		?>
		<header>
			<?php $this->header_content(); ?>
		</header>

		<?php rank_math()->notification->display(); ?>

		<?php $this->show_connect_button(); ?>

		<footer class="form-footer wp-core-ui rank-math-ui">
			<button type="submit" class="button button-<?php echo $this->invalid ? 'secondary' : 'primary alignright'; ?>" formnovalidate id="skip-registration" style="margin-right:15px"><?php echo $this->invalid ? esc_html__( 'Skip Now', 'rank-math' ) : esc_html__( 'Next', 'rank-math' ); ?></button>
		</footer>

		<?php
		$this->print_script();
	}

	/**
	 * Output connect button (instead of the old connect form).
	 */
	private function show_connect_button() {
		?>
		<div class="text-center wp-core-ui rank-math-ui" style="margin-top: 30px;">
			<button type="submit" class="button button-primary button-animated" name="rank_math_activate"><?php echo esc_attr__( 'Connect Your Account', 'rank-math' ); ?></button>
		</div>
		<?php
	}

	/**
	 * Header content.
	 */
	private function header_content() {
		if ( $this->invalid ) :
			?>
			<h1><?php esc_html_e( 'Connect FREE Account', 'rank-math' ); ?></h1>
			<p class="rank-math-gray-box">
				<?php
				/* translators: Link to Free Account Benefits KB article */
				printf( esc_html__( 'By connecting your free account, you get keyword suggestions directly from Google when entering the focus keywords. Not only that, get access to our revolutionary SEO Analyzer inside WordPress that scans your website for SEO errors and suggest improvements. %s', 'rank-math' ), '<a href="' . KB::get( 'free-account-benefits' ) . '" target="_blank">' . esc_html__( 'Read more by following this link.', 'rank-math' ) . '</a>' );
				?>
			</p>
			<?php
			return;
		endif;
		?>

		<h1><?php esc_html_e( 'Account Successfully Connected', 'rank-math' ); ?></h1>
		<h3 style="text-align: center; padding-top:15px;"><?php esc_html_e( 'You have successfully activated Rank Math.', 'rank-math' ); ?></h3>
		<?php
	}

	/**
	 * Execute save handler for current step.
	 */
	public function save_registration() {

		// If no form submission, bail.
		$referer = Param::post( '_wp_http_referer', get_dashboard_url() );
		if ( Param::post( 'step' ) !== 'register' ) {
			return wp_safe_redirect( $referer );
		}

		check_admin_referer( 'rank-math-wizard', 'security' );
		if ( ! Helper::has_cap( 'general' ) ) {
			return wp_safe_redirect( $referer );
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
		wp_safe_redirect( Helper::get_admin_url( 'wizard' ) );
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
		wp_redirect( $url );
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

	/**
	 * Get view file to display.
	 *
	 * @param string $view View to display.
	 * @return string
	 */
	private function get_view( $view ) {
		if ( 'navigation' === $view ) {
			$view = 'no-navigation';
		}

		return rank_math()->admin_dir() . "wizard/views/{$view}.php";
	}

	/**
	 * Print Javascript.
	 */
	private function print_script() {
		?>
		<script>
		(function($){
			$(function() {
				$( '#skip-registration' ).on( 'click', function( event ) {
					$('[name="action"]').val( 'rank_math_skip_wizard' );
				});
			});
		})(jQuery);
		</script>
		<?php
	}
}
