<?php // @codingStandardsIgnoreLine
/**
 * Rank Math SEO Plugin.
 *
 * @package      RANK_MATH
 * @copyright    Copyright (C) 2019-2023, Rank Math - support@rankmath.com
 * @link         https://rankmath.com
 * @since        0.9.0
 *
 * @wordpress-plugin
 * Plugin Name:       Rank Math SEO
 * Version:           1.0.240
 * Plugin URI:        https://rankmath.com/
 * Description:       Rank Math SEO is the Best WordPress SEO plugin with the features of many SEO and AI SEO tools in a single package to help multiply your SEO traffic.
 * Author:            Rank Math SEO
 * Author URI:        https://rankmath.com/?utm_source=Plugin&utm_medium=Readme%20Author%20URI&utm_campaign=WP
 * License:           GPL-3.0+
 * License URI:       https://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       rank-math
 * Domain Path:       /languages
 */

defined( 'ABSPATH' ) || exit;

/**
 * RankMath class.
 *
 * @class Main class of the plugin.
 */
final class RankMath {

	/**
	 * Plugin version.
	 *
	 * @var string
	 */
	public $version = '1.0.240';

	/**
	 * Rank Math database version.
	 *
	 * @var string
	 */
	public $db_version = '1';

	/**
	 * Minimum version of WordPress required to run Rank Math.
	 *
	 * @var string
	 */
	private $wordpress_version = '6.3';

	/**
	 * Minimum version of PHP required to run Rank Math.
	 *
	 * @var string
	 */
	private $php_version = '7.4';

	/**
	 * Holds various class instances.
	 *
	 * @var array
	 */
	private $container = [];

	/**
	 * Hold install error messages.
	 *
	 * @var bool
	 */
	private $messages = [];

	/**
	 * The single instance of the class.
	 *
	 * @var RankMath
	 */
	protected static $instance = null;

	/**
	 * Magic isset to bypass referencing plugin.
	 *
	 * @param  string $prop Property to check.
	 * @return bool
	 */
	public function __isset( $prop ) {
		return isset( $this->{$prop} ) || isset( $this->container[ $prop ] );
	}

	/**
	 * Magic getter method.
	 *
	 * @param  string $prop Property to get.
	 * @return mixed Property value or NULL if it does not exists.
	 */
	public function __get( $prop ) {
		if ( array_key_exists( $prop, $this->container ) ) {
			return $this->container[ $prop ];
		}

		if ( isset( $this->{$prop} ) ) {
			return $this->{$prop};
		}

		return null;
	}

	/**
	 * Magic setter method.
	 *
	 * @param mixed $prop  Property to set.
	 * @param mixed $value Value to set.
	 */
	public function __set( $prop, $value ) {
		if ( property_exists( $this, $prop ) ) {
			$this->$prop = $value;
			return;
		}

		$this->container[ $prop ] = $value;
	}

	/**
	 * Magic call method.
	 *
	 * @param  string $name      Method to call.
	 * @param  array  $arguments Arguments to pass when calling.
	 * @return mixed Return value of the callback.
	 */
	public function __call( $name, $arguments ) {
		$hash = [
			'plugin_dir'   => RANK_MATH_PATH,
			'plugin_url'   => RANK_MATH_URL,
			'includes_dir' => RANK_MATH_PATH . 'includes/',
			'assets'       => RANK_MATH_URL . 'assets/front/',
			'admin_dir'    => RANK_MATH_PATH . 'includes/admin/',
		];

		if ( isset( $hash[ $name ] ) ) {
			return $hash[ $name ];
		}

		return call_user_func_array( $name, $arguments );
	}

	/**
	 * Initialize.
	 */
	public function init() {
	}

	/**
	 * Retrieve main RankMath instance.
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @see rank_math()
	 * @return RankMath
	 */
	public static function get() {
		if ( is_null( self::$instance ) && ! ( self::$instance instanceof RankMath ) ) {
			self::$instance = new RankMath();
			self::$instance->setup();
		}

		return self::$instance;
	}

	/**
	 * Instantiate the plugin.
	 */
	private function setup() {
		// Define plugin constants.
		$this->define_constants();

		if ( ! $this->is_requirements_meet() ) {
			return;
		}

		// Include required files.
		$this->includes();

		// Instantiate classes.
		$this->instantiate();

		// Loaded action.
		do_action( 'rank_math/loaded' );
	}

	/**
	 * Check that the WordPress and PHP setup meets the plugin requirements.
	 *
	 * @return bool
	 */
	private function is_requirements_meet() {

		// Check WordPress version.
		if ( version_compare( get_bloginfo( 'version' ), $this->wordpress_version, '<' ) ) {
			/* translators: WordPress Version */
			$this->messages[] = sprintf( esc_html__( 'You are using the outdated WordPress, please update it to version %s or higher.', 'rank-math' ), $this->wordpress_version );
		}

		// Check PHP version.
		if ( version_compare( phpversion(), $this->php_version, '<' ) ) {
			/* translators: PHP Version */
			$this->messages[] = sprintf( esc_html__( 'Rank Math requires PHP version %s or above. Please update PHP to run this plugin.', 'rank-math' ), $this->php_version );
		}

		if ( empty( $this->messages ) ) {
			return true;
		}

		// Auto-deactivate plugin.
		add_action( 'admin_init', [ $this, 'auto_deactivate' ] );
		add_action( 'admin_notices', [ $this, 'activation_error' ] );

		return false;
	}

	/**
	 * Auto-deactivate plugin if requirements are not met, and display a notice.
	 */
	public function auto_deactivate() {
		deactivate_plugins( plugin_basename( RANK_MATH_FILE ) );
		if ( isset( $_GET['activate'] ) ) { // phpcs:ignore
			unset( $_GET['activate'] ); // phpcs:ignore
		}
	}

	/**
	 * Error notice on plugin activation.
	 */
	public function activation_error() {
		?>
		<div class="notice rank-math-notice notice-error">
			<p>
				<?php echo join( '<br>', $this->messages ); // phpcs:ignore ?>
			</p>
		</div>
		<?php
	}

	/**
	 * Define the plugin constants.
	 */
	private function define_constants() {
		define( 'RANK_MATH_VERSION', $this->version );
		define( 'RANK_MATH_FILE', __FILE__ );
		define( 'RANK_MATH_PATH', dirname( RANK_MATH_FILE ) . '/' );
		define( 'RANK_MATH_URL', plugins_url( '', RANK_MATH_FILE ) . '/' );
		define( 'RANK_MATH_SITE_URL', 'https://rankmath.com' );
		if ( ! defined( 'CONTENT_AI_URL' ) ) {
			define( 'CONTENT_AI_URL', 'https://cai.rankmath.com' );
		}
	}

	/**
	 * Include the required files.
	 */
	private function includes() {
		include __DIR__ . '/vendor/autoload.php';

		// For Theme Developers:
		// theme-folder/rankmath.php will be loaded automatically.
		$file = get_stylesheet_directory() . '/rank-math.php';
		if ( file_exists( $file ) ) {
			require_once $file;
		}
	}

	/**
	 * Instantiate classes.
	 */
	private function instantiate() {
		new \RankMath\Installer();

		// Setting Manager.
		$this->container['settings'] = new \RankMath\Settings();

		// JSON Manager.
		$this->container['json'] = new \RankMath\Json_Manager();

		// Notification Manager.
		$this->container['notification'] = new \RankMath\Admin\Notifications\Notification_Center( 'rank_math_notifications' );

		// Product Registration.
		$this->container['registration'] = new \RankMath\Admin\Registration();
		if ( $this->container['registration']->invalid ) {
			return;
		}

		$this->container['manager']   = new \RankMath\Module\Manager();
		$this->container['variables'] = new \RankMath\Replace_Variables\Manager();

		// Just init without storing it in the container.
		new \RankMath\Common();
		$this->container['rewrite'] = new \RankMath\Rewrite();
		new \RankMath\Compatibility();

		// Frontend SEO Score.
		$this->container['frontend_seo_score'] = new \RankMath\Frontend_SEO_Score();
		$this->load_3rd_party();

		// Initialize the action and filter hooks.
		$this->init_actions();
	}

	/**
	 * Initialize WordPress action and filter hooks.
	 */
	private function init_actions() {
		// Make sure it is loaded before setup_modules and load_modules.
		add_action( 'after_setup_theme', [ $this, 'localization_setup' ], 1 );
		add_action( 'init', [ $this, 'pass_admin_content' ] );
		add_filter( 'cron_schedules', [ $this, 'cron_schedules' ] );

		// Add plugin action links.
		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );
		add_filter( 'plugin_action_links_' . plugin_basename( RANK_MATH_FILE ), [ $this, 'plugin_action_links' ] );
		add_action( 'after_plugin_row_' . plugin_basename( RANK_MATH_FILE ), [ $this, 'plugin_row_deactivate_notice' ] );

		// Booting.
		add_action( 'plugins_loaded', [ $this, 'init' ], 14 );
		add_action( 'rest_api_init', [ $this, 'init_rest_api' ] );

		// Load admin-related functionality.
		if ( is_admin() ) {
			add_action( 'plugins_loaded', [ $this, 'init_admin' ], 15 );
		}

		// Frontend-only functionality.
		if ( ! is_admin() || in_array( \RankMath\Helpers\Param::request( 'action' ), [ 'elementor', 'elementor_ajax' ], true ) ) {
			add_action( 'plugins_loaded', [ $this, 'init_frontend' ], 15 );
		}

		// WP_CLI.
		if ( defined( 'WP_CLI' ) && WP_CLI ) {
			add_action( 'plugins_loaded', [ $this, 'init_wp_cli' ], 20 );
		}
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$controllers = [
			new \RankMath\Rest\Admin(),
			new \RankMath\Rest\Front(),
			new \RankMath\Rest\Shared(),
			new \RankMath\Rest\Post(),
			new \RankMath\Rest\Headless(),
		];

		foreach ( $controllers as $controller ) {
			$controller->register_routes();
		}
	}

	/**
	 * Initialize the admin-related functionality.
	 * Runs on 'plugins_loaded'.
	 */
	public function init_admin() {
		if ( $this->container['registration']->invalid ) {
			return;
		}
		new \RankMath\Admin\Admin_Init();
	}

	/**
	 * Initialize the frontend functionality.
	 * Runs on 'plugins_loaded'.
	 */
	public function init_frontend() {
		if ( $this->container['registration']->invalid ) {
			return;
		}
		$this->container['frontend'] = new \RankMath\Frontend\Frontend();
	}

	/**
	 * Load 3rd party modules.
	 */
	private function load_3rd_party() {
		if ( ! function_exists( 'is_plugin_active' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php'; // @phpstan-ignore-line
		}

		// Elementor.
		if ( is_plugin_active( 'elementor/elementor.php' ) ) {
			new \RankMath\Elementor\Elementor();
		}

		// Divi theme.
		add_action(
			'after_setup_theme',
			function () {
				if ( defined( 'ET_CORE' ) ) {
					new \RankMath\Divi\Divi();
				}
			},
			11
		);
		add_action(
			'current_screen',
			function () {
				if ( defined( 'ET_CORE' ) ) {
					new \RankMath\Divi\Divi_Admin();
				}
			}
		);
	}

	/**
	 * Add our custom WP-CLI commands.
	 */
	public function init_wp_cli() {
		WP_CLI::add_command( 'rankmath sitemap generate', [ '\RankMath\CLI\Commands', 'sitemap_generate' ] );
	}

	/**
	 * Show action links on the plugin screen.
	 *
	 * @param  mixed $links Plugin Action links.
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$options = [
			'options-general' => __( 'Settings', 'rank-math' ),
			'wizard'          => __( 'Setup Wizard', 'rank-math' ),
		];

		if ( $this->container['registration']->invalid ) {
			$options = [
				'registration' => __( 'Setup Wizard', 'rank-math' ),
			];
		}

		foreach ( $options as $link => $label ) {
			$plugin_links[] = '<a href="' . \RankMath\Helper::get_admin_url( $link ) . '">' . esc_html( $label ) . '</a>';
		}

		return array_merge( $links, $plugin_links );
	}

	/**
	 * Add a notice when rank_math_clear_data_on_uninstall filter is present in the theme.
	 *
	 * @param string $file Plugin file.
	 *
	 * @return void
	 */
	public function plugin_row_deactivate_notice( $file ) {
		if ( false === apply_filters( 'rank_math_clear_data_on_uninstall', false ) ) {
			return;
		}

		if ( is_multisite() && ! is_network_admin() && is_plugin_active_for_network( $file ) ) {
			return;
		}

		$wp_list_table = _get_list_table( 'WP_Plugins_List_Table' );
		echo '<tr class="plugin-update-tr active rank-math-deactivate-notice-row" data-slug="" data-plugin="' . esc_attr( $file ) . '" style="position: relative; top: -1px;"><td colspan="' . esc_attr( $wp_list_table->get_column_count() ) . '" class="plugin-update colspanchange"><div class="notice inline notice-error notice-alt"><p>';
		printf(
		/* translators: 1. Bold text 2. Bold text */
			esc_html__( '%1$s A filter to remove the Rank Math data from the database is present. Deactivating & Deleting this plugin will remove everything related to the Rank Math plugin. %2$s', 'rank-math' ),
			'<strong>' . esc_html__( 'CAUTION:', 'rank-math' ) . '</strong>',
			'<br /><strong>' . esc_html__( 'This action is IRREVERSIBLE.', 'rank-math' ) . '</strong>'
		);
		echo '</p></div></td></tr>';
	}

	/**
	 * Add extra links as row meta on the plugin screen.
	 *
	 * @param  mixed $links Plugin Row Meta.
	 * @param  mixed $file  Plugin Base file.
	 * @return array
	 */
	public function plugin_row_meta( $links, $file ) {
		if ( plugin_basename( RANK_MATH_FILE ) !== $file ) {
			return $links;
		}

		$more = [
			'<a href="' . admin_url( '?page=rank-math&view=help' ) . '">' . esc_html__( 'Getting Started', 'rank-math' ) . '</a>',
			'<a href="https://rankmath.com/kb/?utm_source=Plugin&utm_medium=Plugin%20Page%20KB%20Link&utm_campaign=WP" target="_blank">' . esc_html__( 'Documentation', 'rank-math' ) . '</a>',
		];

		return array_merge( $links, $more );
	}

	/**
	 * Initialize plugin for localization.
	 *
	 * Note: the first-loaded translation file overrides any following ones if the same translation is present.
	 *
	 * Locales found in:
	 *     - WP_LANG_DIR/rank-math/rank-math-LOCALE.mo
	 *     - WP_LANG_DIR/plugins/rank-math-LOCALE.mo
	 */
	public function localization_setup() {
		$locale = get_user_locale();
		$locale = apply_filters( 'plugin_locale', $locale, 'rank-math' ); // phpcs:ignore

		unload_textdomain( 'rank-math' );
		if ( false === load_textdomain( 'rank-math', WP_LANG_DIR . '/plugins/seo-by-rank-math-' . $locale . '.mo' ) ) {
			load_textdomain( 'rank-math', WP_LANG_DIR . '/seo-by-rank-math/seo-by-rank-math-' . $locale . '.mo' );
		}
		load_plugin_textdomain( 'rank-math', false, rank_math()->plugin_dir() . 'languages/' );
	}

	/**
	 * Localize admin content to JS
	 */
	public function pass_admin_content() {
		if ( is_user_logged_in() && is_admin_bar_showing() ) {
			$this->container['json']->add( 'version', $this->version, 'rankMath' );
			$this->container['json']->add( 'ajaxurl', admin_url( 'admin-ajax.php' ), 'rankMath' );
			$this->container['json']->add( 'adminurl', admin_url( 'admin.php' ), 'rankMath' );
			$this->container['json']->add( 'endpoint', esc_url_raw( rest_url( 'rankmath/v1' ) ), 'rankMath' );
			$this->container['json']->add( 'security', wp_create_nonce( 'rank-math-ajax-nonce' ), 'rankMath' );
			$this->container['json']->add( 'restNonce', ( wp_installing() && ! is_multisite() ) ? '' : wp_create_nonce( 'wp_rest' ), 'rankMath' );
			$this->container['json']->add( 'modules', \RankMath\Helper::get_active_modules(), 'rankMath' );
		}
	}

	/**
	 * Add cron schedules.
	 *
	 * @param  array $schedules List of schedules for cron jobs.
	 * @return array
	 */
	public function cron_schedules( $schedules ) {
		$schedules['weekly'] = [
			'interval' => DAY_IN_SECONDS * 7,
			'display'  => esc_html__( 'Once Weekly', 'rank-math' ),
		];

		return $schedules;
	}
}

/**
 * Returns the main instance of RankMath to prevent the need to use globals.
 *
 * @return RankMath
 */
function rank_math() { // phpcs:ignore -- This is a main function used to initialize the plugin.
	return RankMath::get();
}

// Start it.
rank_math();
