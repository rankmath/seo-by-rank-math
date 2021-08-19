<?php
/**
 * Instant Indexing module.
 *
 * @since      1.0.56
 * @package    RankMath
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Instant_Indexing;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Module\Base;
use RankMath\Traits\Hooker;
use RankMath\Traits\Ajax;
use RankMath\Admin\Options;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\Url;

defined( 'ABSPATH' ) || exit;

/**
 * Instant_Indexing class.
 */
class Instant_Indexing extends Base {

	use Hooker, Ajax;

	/**
	 * API Key.
	 *
	 * @var string
	 */
	private $api;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();
		$this->api = new Api();

		$this->action( 'admin_enqueue_scripts', 'enqueue', 20 );

		if ( ! self::is_configured() ) {
			return;
		}

		$post_types = Helper::get_settings( 'instant_indexing.bing_post_types', [] );
		foreach ( $post_types as $post_type ) {
			$this->action( 'save_post_' . $post_type, 'save_post', 10, 3 );
			$this->filter( "bulk_actions-edit-{$post_type}", 'post_bulk_actions', 11 );
			$this->filter( "handle_bulk_actions-edit-{$post_type}", 'handle_post_bulk_actions', 10, 3 );
		}

		$this->filter( 'post_row_actions', 'post_row_actions', 10, 2 );
		$this->filter( 'page_row_actions', 'post_row_actions', 10, 2 );
		$this->filter( 'admin_init', 'handle_post_row_actions' );

		$this->ajax( 'instant_indexing_bing_submit_urls', 'ajax_submit_urls' );
		$this->ajax( 'instant_indexing_bing_get_daily_quota', 'ajax_get_daily_quota' );
	}

	/**
	 * Add bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  array $actions Actions.
	 * @return array          New actions.
	 */
	public function post_bulk_actions( $actions ) {
		$actions['rank_math_instant_index'] = esc_html__( 'Instant Indexing: Submit to Bing', 'rank-math' );
		return $actions;
	}

	/**
	 * Action links for the post listing screens.
	 *
	 * @param array  $actions Action links.
	 * @param object $post    Current post object.
	 * @return array
	 */
	public function post_row_actions( $actions, $post ) {
		if ( ! Helper::has_cap( 'general' ) ) {
			return $actions;
		}

		if ( 'publish' !== $post->post_status ) {
			return $actions;
		}

		$post_types = Helper::get_settings( 'instant_indexing.bing_post_types', [] );
		if ( ! in_array( $post->post_type, $post_types, true ) ) {
			return $actions;
		}

		$link = wp_nonce_url(
			add_query_arg(
				[
					'action'        => 'rank_math_instant_index_post',
					'index_post_id' => $post->ID,
					'method'        => 'bing_submit',
				]
			),
			'rank_math_instant_index_post'
		);

		$actions['rmgiapi_bing_submit'] = '<a href="' . $link . '" class="rm-instant-indexing-action rm-instant-indexing-bing-submit">' . __( 'Instant Indexing: Submit to Bing', 'rank-math' ) . '</a>';

		return $actions;
	}

	/**
	 * Handle post row action link actions.
	 *
	 * @return void
	 */
	public function handle_post_row_actions() {
		if ( 'rank_math_instant_index_post' !== Param::get( 'action' ) ) {
			return;
		}

		$post_id = absint( Param::get( 'index_post_id' ) );
		if ( ! $post_id ) {
			return;
		}

		if ( ! wp_verify_nonce( Param::get( '_wpnonce' ), 'rank_math_instant_index_post' ) ) {
			return;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return;
		}

		$data = $this->api->submit_url( get_permalink( $post_id ) );
		if ( $data['message'] ) {
			$notification_type = ( 'ok' === $data['status'] ? 'success' : 'error' );
			Helper::add_notification( $data['message'], [ 'type' => $notification_type ] );
		}

		Helper::redirect( remove_query_arg( [ 'action', 'index_post_id', 'method', '_wpnonce' ] ) );
		exit;
	}

	/**
	 * Handle bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  string $redirect   Redirect URL.
	 * @param  string $doaction   Performed action.
	 * @param  array  $object_ids Post IDs.
	 *
	 * @return string             New redirect URL.
	 */
	public function handle_post_bulk_actions( $redirect, $doaction, $object_ids ) {
		if ( 'rank_math_instant_index' !== $doaction || empty( $object_ids ) ) {
			return $redirect;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return $redirect;
		}

		$urls = [];
		foreach ( $object_ids as $object_id ) {
			$urls[] = get_permalink( $object_id );
		}

		$data = $this->api->batch_submit_urls( $urls );
		if ( $data['message'] ) {
			$notification_type = ( 'ok' === $data['status'] ? 'success' : 'error' );
			Helper::add_notification( $data['message'], [ 'type' => $notification_type ] );
		}

		return $redirect;
	}

	/**
	 * Ajax handler to send multiple URLs for Instant indexing.
	 */
	public function ajax_submit_urls() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$urls = explode( "\n", str_replace( "\r", '', Param::post( 'urls' ) ) );

		// Filter external URLs.
		$urls = array_filter(
			$urls,
			function( $url ) {
				return ! Url::is_external( $url );
			}
		);

		// Trim whitespace.
		$urls = array_map( 'trim', $urls );

		// Filter empty items.
		$urls = array_filter( $urls );

		$request = $this->api->batch_submit_urls( $urls );

		$this->send( $request, ( 'ok' === $request['status'] ) );
	}

	/**
	 * Ajax handler to get remaining quota.
	 */
	public function ajax_get_daily_quota() {
		$this->verify_nonce( 'rank-math-ajax-nonce' );
		$this->has_cap_ajax( 'general' );

		$request = $this->api->get_daily_quota();

		$this->send( $request, ( 'ok' === $request['status'] ) );
	}

	/**
	 * Register admin page.
	 */
	public function register_admin_page() {
		$tabs = [
			'settings'       => [
				'icon'  => 'rm-icon rm-icon-settings',
				'title' => esc_html__( 'Settings', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'  => sprintf( esc_html__( 'Instant Indexing module settings. %s.', 'rank-math' ), '<a href="' . KB::get( 'bing-instant-indexing' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'file'  => dirname( __FILE__ ) . '/views/options.php',
			],
			'url-submission' => [
				'icon'    => 'rm-icon rm-icon-instant-indexing',
				'title'   => esc_html__( 'URL Submission', 'rank-math' ),
				'desc'    => esc_html__( 'Send URLs directly to the Bing URL Submission API.', 'rank-math' ) . ' <a href="' . KB::get( 'bing-instant-indexing' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>',
				'classes' => 'rank-math-advanced-option',
				'file'    => dirname( __FILE__ ) . '/views/console.php',
			],
		];

		if ( self::is_configured() ) {
			$tabs = array_reverse( $tabs, true );
		}

		/**
		 * Allow developers to add new sections in the General Settings.
		 *
		 * @param array $tabs
		 */
		$tabs = $this->do_filter( 'settings/instant_indexing', $tabs );

		new Options(
			[
				'key'        => 'rank-math-options-instant-indexing',
				'title'      => esc_html__( 'Instant Indexing', 'rank-math' ),
				'menu_title' => esc_html__( 'Instant Indexing', 'rank-math' ),
				'capability' => 'rank_math_general',
				'tabs'       => $tabs,
				'position'   => 100,
			]
		);
	}

	/**
	 * When a post from a watched post type is published or updated, submit its URL
	 * to the API and add notice about it.
	 *
	 * @param  int    $post_id Post ID.
	 * @param  object $post    Post object.
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post ) {
		if ( 'publish' !== $post->post_status ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) ) {
			return;
		}

		$send_url = $this->do_filter( 'instant_indexing/publish_url', get_permalink( $post ), $post, 'bing' );

		// Early exit if filter is set to false.
		if ( ! $send_url ) {
			return;
		}

		$data = $this->api->submit_url( $send_url );
		if ( $data['message'] ) {
			$notification_type = ( 'ok' === $data['status'] ? 'success' : 'error' );
			Helper::add_notification( $data['message'], [ 'type' => $notification_type ] );
		}
	}

	/**
	 * Is module configured.
	 *
	 * @return boolean
	 */
	public static function is_configured() {
		return (bool) Helper::get_settings( 'instant_indexing.bing_api_key' );
	}

	/**
	 * Enqueue CSS & JS.
	 *
	 * @param string $hook Page hook name.
	 * @return void
	 */
	public function enqueue( $hook ) {
		if ( 'rank-math_page_rank-math-options-instant-indexing' !== $hook ) {
			return;
		}

		$uri = untrailingslashit( plugin_dir_url( __FILE__ ) );
		wp_enqueue_script( 'rank-math-instant-indexing', $uri . '/assets/js/instant-indexing.js', [ 'jquery' ], rank_math()->version, true );

		Helper::add_json( 'is_instant_indexing_configured', self::is_configured() );
	}

}
