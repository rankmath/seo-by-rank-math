<?php
/**
 * The Module
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\Module
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Module;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Conditional;

defined( 'ABSPATH' ) || exit;

/**
 * Manager class.
 */
class Manager {

	use Hooker;

	/**
	 * Holds modules.
	 *
	 * @var array
	 */
	public $modules = [];

	/**
	 * Holds module objects.
	 *
	 * @var array
	 */
	private $controls = [];

	/**
	 * The Constructor.
	 */
	public function __construct() {
		if ( Conditional::is_heartbeat() ) {
			return;
		}

		$this->action( 'plugins_loaded', 'setup_modules' );
		$this->filter( 'rank_math/modules', 'setup_core', 1 );
		$this->filter( 'rank_math/modules', 'setup_admin_only', 1 );
		$this->filter( 'rank_math/modules', 'setup_internals', 1 );
		$this->filter( 'rank_math/modules', 'setup_3rd_party', 1 );

		$this->action( 'plugins_loaded', 'load_modules', 11 );
		add_action( 'rank_math/module_changed', [ '\RankMath\Admin\Watcher', 'module_changed' ], 10, 2 );
	}

	/**
	 * Include default modules support.
	 */
	public function setup_modules() {
		/**
		 * Filters the array of modules available to be activated.
		 *
		 * @param array $modules Array of available modules.
		 */
		$modules = $this->do_filter( 'modules', [] );

		ksort( $modules );
		foreach ( $modules as $id => $module ) {
			$this->add_module( $id, $module );
		}
	}

	/**
	 * Setup core modules.
	 *
	 * @param array $modules Array of modules.
	 *
	 * @return array
	 */
	public function setup_core( $modules ) {
		$modules['404-monitor'] = [
			'title'    => esc_html__( '404 Monitor', 'rank-math' ),
			'desc'     => esc_html__( 'Records the URLs on which visitors & search engines run into 404 Errors. You can also turn on Redirections to redirect the error causing URLs to other URLs.', 'rank-math' ),
			'class'    => 'RankMath\Monitor\Monitor',
			'icon'     => '404',
			'settings' => Helper::get_admin_url( 'options-general' ) . '#setting-panel-404-monitor',
		];

		$modules['local-seo'] = [
			'title'    => esc_html__( 'Local SEO & Knowledge Graph', 'rank-math' ),
			'desc'     => esc_html__( 'Dominate the search results for the local audiences by optimizing your website for Local SEO and it also helps you to add code related to Knowledge Graph.', 'rank-math' ),
			'class'    => 'RankMath\Local_Seo\Local_Seo',
			'icon'     => 'local-seo',
			'settings' => Helper::get_admin_url( 'options-titles' ) . '#setting-panel-local',
		];

		$modules['redirections'] = [
			'title'    => esc_html__( 'Redirections', 'rank-math' ),
			'desc'     => esc_html__( 'Redirect non-existent content easily with 301 and 302 status code. This can help improve your site ranking. Also supports many other response codes.', 'rank-math' ),
			'class'    => 'RankMath\Redirections\Redirections',
			'icon'     => 'redirection',
			'settings' => Helper::get_admin_url( 'options-general' ) . '#setting-panel-redirections',
		];

		$modules['rich-snippet'] = [
			'title'    => esc_html__( 'Schema (Structured Data)', 'rank-math' ),
			'desc'     => esc_html__( 'Enable support for the structured data, which adds Schema code in your website, resulting in rich search results, better CTR and more traffic.', 'rank-math' ),
			'class'    => 'RankMath\RichSnippet\RichSnippet',
			'icon'     => 'schema',
			'settings' => Helper::get_admin_url( 'options-titles' ) . '#setting-panel-post-type-post',
		];

		$modules['sitemap'] = [
			'title'    => esc_html__( 'Sitemap', 'rank-math' ),
			'desc'     => esc_html__( 'Enable Rank Math\'s sitemap feature, which helps search engines intelligently crawl your website\'s content. It also supports hreflang tag.', 'rank-math' ),
			'class'    => 'RankMath\Sitemap\Sitemap',
			'icon'     => 'sitemap',
			'settings' => Helper::get_admin_url( 'options-sitemap' ),
		];

		$modules['link-counter'] = [
			'title' => esc_html__( 'Link Counter', 'rank-math' ),
			'desc'  => esc_html__( 'Counts the total number of internal, external links, to and from links inside your posts. You can also see the same count in the Posts List Page.', 'rank-math' ),
			'class' => 'RankMath\Links\Links',
			'icon'  => 'link',
		];

		$modules['image-seo'] = [
			'title'    => esc_html__( 'Image SEO', 'rank-math' ),
			'desc'     => esc_html__( 'Advanced Image SEO options to supercharge your website. Automate the task of adding the ALT and Title tags to your images on the fly.', 'rank-math' ),
			'class'    => 'RankMath\Image_Seo\Image_Seo',
			'icon'     => 'images',
			'settings' => Helper::get_admin_url( 'options-general' ) . '#setting-panel-images',
		];

		return $modules;
	}

	/**
	 * Setup admin only modules.
	 *
	 * @param array $modules Array of modules.
	 *
	 * @return array
	 */
	public function setup_admin_only( $modules ) {

		$modules['role-manager'] = [
			'title'    => esc_html__( 'Role Manager', 'rank-math' ),
			'desc'     => esc_html__( 'The Role Manager allows you to use WordPress roles to control which of your site users can have edit or view access to Rank Math\'s settings.', 'rank-math' ),
			'class'    => 'RankMath\Role_Manager\Role_Manager',
			'icon'     => 'role-manager',
			'only'     => 'admin',
			'settings' => Helper::get_admin_url( 'role-manager' ),
		];

		$modules['search-console'] = [
			'title'    => esc_html__( 'Search Console', 'rank-math' ),
			'desc'     => esc_html__( 'Connect Rank Math with Google Search Console to see the most important information from Google directly in your WordPress dashboard.', 'rank-math' ),
			'class'    => 'RankMath\Search_Console\Search_Console',
			'icon'     => 'search-console',
			'only'     => 'admin',
			'settings' => Helper::get_admin_url( 'options-general' ) . '#setting-panel-search-console',
		];

		$modules['seo-analysis'] = [
			'title'    => esc_html__( 'SEO Analysis', 'rank-math' ),
			'desc'     => esc_html__( 'Let Rank Math analyze your website and your website\'s content using 70+ different tests to provide tailor-made SEO Analysis to you.', 'rank-math' ),
			'class'    => 'RankMath\SEO_Analysis\SEO_Analysis',
			'icon'     => 'analyzer',
			'only'     => 'admin',
			'settings' => Helper::get_admin_url( 'seo-analysis' ),
		];

		return $modules;
	}

	/**
	 * Setup internal modules.
	 *
	 * @param array $modules Array of modules.
	 *
	 * @return array
	 */
	public function setup_internals( $modules ) {

		$modules['robots-txt'] = [
			'title' => esc_html__( 'Robotx Txt', 'rank-math' ),
			'only'  => 'internal',
			'class' => 'RankMath\Robots_Txt',
		];

		$modules['version-control'] = [
			'title' => esc_html__( 'Version Control', 'rank-math' ),
			'only'  => 'internal',
			'class' => 'RankMath\Version_Control',
		];

		$modules['database-tools'] = [
			'title' => esc_html__( 'Database Tools', 'rank-math' ),
			'only'  => 'internal',
			'class' => 'RankMath\Tools\Database_Tools',
		];

		$modules['status'] = [
			'title' => esc_html__( 'Status', 'rank-math' ),
			'only'  => 'internal',
			'class' => 'RankMath\Status\Status',
		];

		return $modules;
	}

	/**
	 * Setup 3rd party modules.
	 *
	 * @param array $modules Array of modules.
	 *
	 * @return array
	 */
	public function setup_3rd_party( $modules ) {

		$modules['amp'] = [
			'title' => esc_html__( 'AMP', 'rank-math' ),
			'desc'  => sprintf(
				/* translators: Link to AMP plugin */
				esc_html__( 'Install %s to make Rank Math work with Accelerated Mobile Pages. Rank Math automatically adds required meta tags in all the AMP pages.', 'rank-math' ),
				'<a href="' . Helper::get_admin_url( 'help#help-panel-amp' ) . '">' . esc_html__( 'AMP plugin', 'rank-math' ) . '</a>'
			),
			'icon'  => 'mobile',
			'only'  => 'skip',
		];

		$modules['bbpress'] = [
			'title'         => esc_html__( 'bbPress', 'rank-math' ),
			'desc'          => esc_html__( 'Add proper Meta tags to your bbPress forum posts, categories, profiles, etc. Get more options to take control of what search engines see and how they see it.', 'rank-math' ),
			'icon'          => 'users',
			'disabled'      => ( ! function_exists( 'is_bbpress' ) ),
			'disabled_text' => esc_html__( 'Please activate bbPress plugin to use this module.', 'rank-math' ),
			'only'          => 'skip',
		];

		$modules['buddypress'] = [
			'title'         => esc_html__( 'BuddyPress', 'rank-math' ),
			'desc'          => esc_html__( 'Enable the BuddyPress module for Rank Math SEO to make your BuddyPress forum SEO friendly by adding proper meta tags to all forum pages.', 'rank-math' ),
			'icon'          => 'comments',
			'class'         => 'RankMath\BuddyPress\BuddyPress',
			'disabled'      => ! class_exists( 'BuddyPress' ),
			'disabled_text' => esc_html__( 'Please activate BuddyPress plugin to use this module.', 'rank-math' ),
		];

		$modules['woocommerce'] = [
			'title'         => esc_html__( 'WooCommerce', 'rank-math' ),
			'desc'          => esc_html__( 'Optimize WooCommerce Pages for Search Engines by adding required metadata and Product Schema which will make your site stand out in the SERPs.', 'rank-math' ),
			'class'         => 'RankMath\WooCommerce\WooCommerce',
			'icon'          => 'cart',
			'disabled'      => ( ! Conditional::is_woocommerce_active() ),
			'disabled_text' => esc_html__( 'Please activate WooCommerce plugin to use this module.', 'rank-math' ),
		];

		$modules['acf'] = [
			'title'         => esc_html__( 'ACF', 'rank-math' ),
			'desc'          => esc_html__( 'ACF support helps Rank Math SEO read and analyze content written in the Advanced Custom Fields. If your theme uses ACF, you should enable this option.', 'rank-math' ),
			'class'         => 'RankMath\ACF\ACF',
			'icon'          => 'acf',
			'disabled'      => ( ! function_exists( 'acf' ) ),
			'disabled_text' => esc_html__( 'Please activate ACF plugin to use this module.', 'rank-math' ),
		];

		$modules['web-stories'] = [
			'title'         => esc_html__( 'Google Web Stories', 'rank-math' ),
			'desc'          => esc_html__( 'Make any Story created with the Web Stories WordPress plugin SEO-Ready with automatic support for Schema and Meta tags.', 'rank-math' ),
			'class'         => 'RankMath\Web_Stories\Web_Stories',
			'icon'          => 'stories',
			'disabled'      => ( ! defined( 'WEBSTORIES_VERSION' ) ),
			'disabled_text' => esc_html__( 'Please activate Web Stories plugin to use this module.', 'rank-math' ),
		];

		return $modules;
	}

	/**
	 * Add module.
	 *
	 * @param string $id   Module unique id.
	 * @param array  $args Module configuration.
	 */
	public function add_module( $id, $args = [] ) {
		$this->modules[ $id ] = new Module( $id, $args );
	}

	/**
	 * Display module form to enable/disable them.
	 *
	 * @codeCoverageIgnore
	 */
	public function display_form() {
		if ( ! current_user_can( 'manage_options' ) ) {
			echo 'You cant access this page.';
			return;
		}
		?>
		<div class="rank-math-ui module-listing dashboard-wrapper">

			<div class="grid">
			<?php
			foreach ( $this->modules as $module ) :
				if ( ! $module->can_display() ) {
					continue;
				}

				$is_active   = $module->is_active();
				$is_disabled = $module->is_disabled();
				$is_hidden   = $module->is_hidden();
				?>
				<div class="rank-math-box <?php echo $is_active ? 'active' : ''; ?> <?php echo $is_hidden ? 'hidden' : ''; ?>">

					<i class="rm-icon rm-icon-<?php echo $module->get_icon(); ?>"></i>

					<header>

						<h3><?php echo $module->get( 'title' ); ?></h3>

						<p><?php echo $module->get( 'desc' ); ?></p>

					</header>

					<div class="status wp-clearfix">

						<?php $module->the_link(); ?>

						<span class="cmb2-toggle">
							<input type="checkbox" class="rank-math-modules" id="module-<?php echo $module->get_id(); ?>" name="modules[]" value="<?php echo $module->get_id(); ?>"<?php checked( $is_active ); ?> <?php disabled( $is_disabled, true ); ?>>
							<label for="module-<?php echo $module->get_id(); ?>" class="cmb2-slider <?php echo $is_disabled ? 'rank-math-tooltip' : ''; ?>">
								<?php echo $module->has( 'disabled_text' ) ? '<span>' . $module->get( 'disabled_text' ) . '</span>' : ''; ?>
								<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
								<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
							</label>
							<span class="input-loading"></span>
						</span>

					</div>

				</div>
			<?php endforeach; ?>
			</div>

		</div>
		<?php
	}

	/**
	 * Load active modules.
	 */
	public function load_modules() {
		foreach ( $this->modules as $id => $module ) {
			if ( false === $module->can_load_module() ) {
				continue;
			}

			$this->load_module( $id, $module );
		}
	}

	/**
	 * Load single module.
	 *
	 * @param string $id ID of module.
	 * @param Module $module Module instance.
	 */
	private function load_module( $id, $module ) {
		$object_class = $module->get( 'class' );
		if ( $module->is_admin() ) {
			$this->load_module_common( $module );
			if ( ! is_admin() ) {
				return;
			}
		}

		if ( class_exists( $object_class ) ) {
			$this->controls[ $id ] = new $object_class;
		}
	}

	/**
	 * Load module common file.
	 *
	 * @param Module $module Module instance.
	 */
	public function load_module_common( $module ) {
		$object_class = $module->get( 'class' );
		if ( class_exists( $object_class . '_Common' ) ) {
			$module_common_class                             = $object_class . '_Common';
			$this->controls[ $module->get_id() . '_common' ] = new $module_common_class;
		}
	}

	/**
	 * Get module by ID.
	 *
	 * @param string $id ID to get module.
	 *
	 * @return object Module class object.
	 */
	public function get_module( $id ) {
		return isset( $this->controls[ $id ] ) ? $this->controls[ $id ] : false;
	}
}
