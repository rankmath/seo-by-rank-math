<?php
/**
 * Rank Math Unit Tests Bootstrap
 *
 * @package RankMath\Tests
 */

defined( 'ABSPATH' ) || exit;

class Rank_Math_Unit_Tests_Bootstrap {

	/** @var Rank_Math_Unit_Tests_Bootstrap instance */
	protected static $instance = null;

	/** @var string directory where wordpress-tests-lib is installed */
	public $wp_tests_dir;

	/** @var string testing directory */
	public $tests_dir;

	/** @var string plugin directory */
	public $plugin_dir;

	/**
	 * Setup the unit testing environment.
	 */
	public function __construct() {

		// phpcs:disable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions
		ini_set( 'display_errors', 'on' );
		error_reporting( E_ALL );
		// phpcs:enable WordPress.PHP.DiscouragedPHPFunctions, WordPress.PHP.DevelopmentFunctions

		// Ensure server variable is set for WP email functions.
		// phpcs:disable WordPress.VIP.SuperGlobalInputUsage.AccessDetected
		if ( ! isset( $_SERVER['SERVER_NAME'] ) ) {
			$_SERVER['SERVER_NAME'] = 'localhost';
		}
		// phpcs:enable WordPress.VIP.SuperGlobalInputUsage.AccessDetected

		define( 'WC_DOING_PHPUNIT', true );
		echo 'Welcome to the Rank Math SEO Test Suite' . PHP_EOL;
		echo 'Version: 1.0' . PHP_EOL . PHP_EOL;

		$this->tests_dir    = dirname( __FILE__ );
		$this->plugin_dir   = dirname( $this->tests_dir );
		$this->wp_tests_dir = getenv( 'WP_TESTS_DIR' ) ? getenv( 'WP_TESTS_DIR' ) : '/tmp/wordpress-tests-lib';

		// load test function so tests_add_filter() is available
		require_once $this->wp_tests_dir . '/includes/functions.php';

		// load WC
		tests_add_filter( 'muplugins_loaded', array( $this, 'load_rank_math' ) );

		// install WC
		tests_add_filter( 'setup_theme', array( $this, 'install_rank_math' ) );

		// load the WP testing environment
		require_once $this->wp_tests_dir . '/includes/bootstrap.php';

		// load testing framework
		$this->includes();
	}

	/**
	 * Load Rank Math.
	 */
	public function load_rank_math() {
		require_once $this->plugin_dir . '/rank-math.php';
	}

	/**
	 * Install Rank Math after the test environment and Rank Math have been loaded.
	 */
	public function install_rank_math() {

		// Clean existing install first.
		define( 'WP_UNINSTALL_PLUGIN', true );
		define( 'WC_REMOVE_ALL_DATA', true );
		include $this->plugin_dir . '/uninstall.php';
		$installer = new \RankMath\Installer;
		$installer->activation();

		echo esc_html( 'Installing Rank Math...' . PHP_EOL );
	}

	/**
	 * Load test caes and factories.
	 */
	public function includes() {
		require dirname( __FILE__ ) . '/framework/class-unit-test-case.php';
	}

	/**
	 * Get the single class instance.
	 *
	 * @return Rank_Math_Unit_Tests_Bootstrap
	 */
	public static function instance() {
		if ( is_null( self::$instance ) ) {
			self::$instance = new self();
		}
		return self::$instance;
	}
}

Rank_Math_Unit_Tests_Bootstrap::instance();
