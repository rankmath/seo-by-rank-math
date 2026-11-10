<?php
/**
 * Registers the "Help & Support" chat page via the WAP client library.
 *
 * @since      1.0.282
 * @package    RankMath
 * @subpackage RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Agent;

use RankMath\Helper;
use RankMath\KB;
use RankMath\Helpers\Param;
use RankMath\Traits\Hooker;
use RankMath\Traits\Grnd_Provider;
use RankMath\Traits\Connect_Cta;
use GroupOne\WapClient\ChatWidget;
use GroupOne\WapClient\AppPasswordManager;

defined( 'ABSPATH' ) || exit;

/**
 * Support_Agent class.
 */
class Support_Agent {

	use Hooker;
	use Grnd_Provider;
	use Connect_Cta;

	/**
	 * Admin page slug.
	 */
	const PAGE_SLUG = 'rank-math-support-agent';

	/**
	 * Product slug. Must match a role mapped on the WAP backend.
	 */
	const PRODUCT = Agent::PRODUCT;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/admin_pages', 'add_admin_page' );
		$this->filter( 'admin_body_class', 'add_body_class' );
		$this->action( 'init', 'register_chat_page' );
		$this->action( 'admin_enqueue_scripts', 'enqueue_header_assets' );

		// WapClient owns the page's render callback, so there's no PHP hook to
		// echo a header div through — in_admin_header fires before it regardless.
		$this->action( 'in_admin_header', 'render_header_container' );
	}

	/**
	 * Allow the Support Agent page slug through Rank Math's page allowlist.
	 *
	 * @param array $pages Existing allowed page slugs.
	 *
	 * @return array
	 */
	public function add_admin_page( $pages ) {
		$pages[] = 'rank-math_page_' . self::PAGE_SLUG;
		return $pages;
	}

	/**
	 * Enqueue the DashboardHeader bundle, only on the Support Agent page.
	 */
	public function enqueue_header_assets() {
		if ( ! $this->is_support_page() ) {
			return;
		}

		// Keep Connect your account CTA styles page-specific in setup-wizard.css, not common.css.
		wp_enqueue_style( 'rank-math-wizard', rank_math()->plugin_url() . 'assets/admin/css/setup-wizard.css', [ 'rank-math-common' ], rank_math()->version );

		wp_enqueue_script(
			'rank-math-support-agent',
			RANK_MATH_URL . 'includes/modules/agent/assets/js/support-agent.js',
			[ 'wp-element', 'rank-math-components' ],
			rank_math()->version,
			true
		);
	}

	/**
	 * Echo the DashboardHeader mount point before WapClient renders its own markup.
	 */
	public function render_header_container() {
		if ( ! $this->is_support_page() ) {
			return;
		}

		echo '<div id="rank-math-support-agent-header"></div>';
	}

	/**
	 * Add the rank-math-page class so shared admin styles (e.g. breadcrumbs) apply.
	 *
	 * @param string $classes Space-separated list of body classes.
	 *
	 * @return string
	 */
	public function add_body_class( $classes ) {
		if ( ! $this->is_support_page() ) {
			return $classes;
		}

		return $classes . ' rank-math-page';
	}

	/**
	 * Register the Support Agent chat page.
	 *
	 * Stays on `init`, not `admin_menu`: the permission endpoint's id allowlist is
	 * the live page registry, and `admin_menu` never fires on admin-ajax.php, so
	 * rows registered there would render but answer 400 on every save.
	 */
	public function register_chat_page() {
		if ( ! class_exists( '\WapClient' ) ) {
			return;
		}

		\WapClient::register_chat_page(
			[
				'menu_slug'       => self::PAGE_SLUG,
				'parent_slug'     => 'rank-math',
				'page_title'      => '',
				'menu_title'      => esc_html__( 'Help & Support', 'seo-by-rank-math' ),
				'render'          => [ $this, 'render_page' ],
				'product'         => self::PRODUCT,
				'capability'      => 'manage_options',
				'server_url'      => Agent::get_server_url(),
				'page_context'    => 'standard',
				'grnd_provider'   => [ $this, 'acquire_grnd' ],
				'terms_url'       => KB::get( 'terms-and-conditions', 'Support Agent Terms Link' ),
				'site_access_url' => KB::get( 'assistant-site-access', 'Support Agent Site Access Link' ),
				'privacy_url'     => KB::get( 'privacy-policy', 'Support Agent Privacy Policy Link' ),
				'brand'           => 'rankmath',
				'layout'          => [
					// Required by the Permissions rows below: with the gear hidden the
					// settings page can never be opened, so the rows would be unreachable.
					'showSettings'      => true,
					// The header carries only New chat and Settings.
					'expandToggle'      => 'off',
					// Hidden on every Rank Math surface for now.
					'showAgentSelector' => false,
				],
				'i18n'            => Agent::get_i18n(),
				'permissions'     => self::get_permission_rows(),
			]
		);
	}

	/**
	 * Permission rows for the widget's settings sheet.
	 *
	 * Ids are bare here and in the issuer request body; the GRND issuer adds the
	 * `{product}:` namespace, so a site can never grant into another product's
	 * namespace. Managing the plugin defaults to allow, as the Setup Wizard shares
	 * these values without showing the settings; managing the site defaults to deny.
	 *
	 * Public so other surfaces on the same product (PRO's Content AI embed) register
	 * the identical ids: the library keys rows by id per product, so one definition
	 * means one set of stored values rather than two that disagree.
	 *
	 * @return array[]
	 */
	public static function get_permission_rows() {
		return [
			[
				'id'           => 'manage-plugin',
				'label'        => __( 'Manage your Rank Math plugin', 'seo-by-rank-math' ),
				'hint'         => __( 'Let the agent view and update your Rank Math settings.', 'seo-by-rank-math' ),
				'options'      => [
					[
						'value' => 'allow',
						'label' => __( 'Allow', 'seo-by-rank-math' ),
					],
					[
						'value' => 'ask',
						'label' => __( 'Ask permission', 'seo-by-rank-math' ),
					],
					[
						'value' => 'deny',
						'label' => __( 'Deny', 'seo-by-rank-math' ),
					],
				],
				'defaultValue' => 'allow',
			],
			[
				'id'           => 'manage-site',
				'label'        => __( 'Manage your WordPress site', 'seo-by-rank-math' ),
				'hint'         => __( 'Let the agent make changes directly on your WordPress site.', 'seo-by-rank-math' ),
				'badge'        => __( 'Coming soon', 'seo-by-rank-math' ),
				// A single option renders the row read-only: this permission cannot be
				// granted yet, so offering a choice that does nothing would mislead.
				'options'      => [
					[
						'value' => 'deny',
						'label' => __( 'Deny', 'seo-by-rank-math' ),
					],
				],
				'defaultValue' => 'deny',
			],
		];
	}

	/**
	 * Render the page body: chat widget, or the Connect CTA when not connected.
	 *
	 * @param array $config Credential-free page config from ChatWidget.
	 */
	public function render_page( $config ) {
		if ( ! Helper::is_site_connected() ) {
			$this->render_connect_cta(
				__( 'Connect your Rank Math account for free to start using the Help & Support assistant.', 'seo-by-rank-math' ),
				self::PAGE_SLUG
			);
			return;
		}

		// Application Passwords are the credential the assistant authenticates with;
		// without them the widget can only fail silently, so explain why instead.
		if ( ! AppPasswordManager::are_app_passwords_available() ) {
			$this->render_unavailable_cta();
			return;
		}

		ChatWidget::render_chat_root( $config['menu_slug'] );
	}

	/**
	 * GRND provider callback: mints, seals, and exchanges the credential for a GRND.
	 *
	 * @return array{grnd:string,expires_at:int}|\WP_Error
	 */
	public function acquire_grnd() {
		return $this->acquire_grnd_for( self::PRODUCT, 'Rank Math Support Agent' );
	}

	/**
	 * Whether the current admin screen is the Support Agent page.
	 *
	 * @return bool
	 */
	private function is_support_page() {
		return self::PAGE_SLUG === Param::get( 'page' );
	}
}
