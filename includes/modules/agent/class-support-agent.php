<?php
/**
 * Registers the "Help & Support" chat page via the WAP client library.
 *
 * @since      1.0.277
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
	 * WAP backend base URL (wrap-key + chat host).
	 */
	const SERVER_URL = Agent::SERVER_URL;

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
	 */
	public function register_chat_page() {
		if ( ! class_exists( '\WapClient' ) ) {
			return;
		}

		\WapClient::register_chat_page(
			[
				'menu_slug'     => self::PAGE_SLUG,
				'parent_slug'   => 'rank-math',
				'page_title'    => '',
				'menu_title'    => esc_html__( 'Help & Support', 'seo-by-rank-math' ),
				'render'        => [ $this, 'render_page' ],
				'product'       => self::PRODUCT,
				'server_url'    => self::SERVER_URL,
				'page_context'  => 'standard',
				'grnd_provider' => [ $this, 'acquire_grnd' ],
				'terms_url'     => KB::get( 'terms-and-conditions', 'Support Agent Terms Link' ),
				'layout'        => [
					'showSettings' => false,
				],
			]
		);
	}

	/**
	 * Render the page body: chat widget, or the Connect CTA when not connected.
	 *
	 * @param array $config Credential-free page config from ChatWidget.
	 */
	public function render_page( $config ) {
		if ( Helper::is_site_connected() ) {
			ChatWidget::render_chat_root( $config['menu_slug'] );
			return;
		}

		$this->render_connect_cta(
			__( 'Connect your Rank Math account for free to start using the Help & Support assistant.', 'seo-by-rank-math' ),
			self::PAGE_SLUG
		);
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
