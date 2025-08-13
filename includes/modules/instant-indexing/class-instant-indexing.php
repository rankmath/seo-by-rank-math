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
use RankMath\Admin\Register_Options_Page;
use RankMath\Helpers\Param;
use RankMath\Helpers\Sitepress;

defined( 'ABSPATH' ) || exit;

/**
 * Instant_Indexing class.
 */
class Instant_Indexing extends Base {

	use Hooker;
	use Ajax;

	/**
	 * API Object.
	 *
	 * @var string
	 */
	private $api;

	/**
	 * Keep log of submitted objects to avoid double submissions.
	 *
	 * @var array
	 */
	private $submitted = [];

	/**
	 * Store previous post status that we can check agains in save_post.
	 *
	 * @var array
	 */
	private $previous_post_status = [];

	/**
	 * Store original permalinks for when they get trashed.
	 *
	 * @var array
	 */
	private $previous_post_permalinks = [];

	/**
	 * Restrict to one request every X seconds to a given URL.
	 */
	const THROTTLE_LIMIT = 5;

	/**
	 * Constructor.
	 */
	public function __construct() {
		parent::__construct();

		if ( ! $this->is_configured() ) {
			Api::get()->reset_key();
		}

		$this->action( 'init', 'register_instant_indexing_settings', 125 );
		$post_types = $this->get_auto_submit_post_types();
		if ( ! empty( $post_types ) ) {
			$this->filter( 'wp_insert_post_data', 'before_save_post', 10, 4 );
		}

		foreach ( $post_types as $post_type ) {
			$this->action( 'save_post_' . $post_type, 'save_post', 10, 3 );
			$this->filter( "bulk_actions-edit-{$post_type}", 'post_bulk_actions', 11 );
			$this->filter( "handle_bulk_actions-edit-{$post_type}", 'handle_post_bulk_actions', 10, 3 );
		}

		$this->filter( 'post_row_actions', 'post_row_actions', 10, 2 );
		$this->filter( 'page_row_actions', 'post_row_actions', 10, 2 );
		$this->filter( 'admin_init', 'handle_post_row_actions' );

		$this->action( 'wp', 'serve_api_key' );
		$this->action( 'rest_api_init', 'init_rest_api' );
	}

	/**
	 * Load the REST API endpoints.
	 */
	public function init_rest_api() {
		$rest = new Rest();
		$rest->register_routes();
	}

	/**
	 * Add bulk actions for applicable posts, pages, CPTs.
	 *
	 * @param  array $actions Actions.
	 * @return array          New actions.
	 */
	public function post_bulk_actions( $actions ) {
		$actions['rank_math_indexnow'] = esc_html__( 'Instant Indexing: Submit Pages', 'rank-math' );
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

		$post_types = $this->get_auto_submit_post_types();
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

		$actions['indexnow_submit'] = '<a href="' . esc_url( $link ) . '" class="rm-instant-indexing-action rm-indexnow-submit">' . __( 'Instant Indexing: Submit Page', 'rank-math' ) . '</a>';

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

		$this->api_submit( get_permalink( $post_id ), true );

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
		if ( 'rank_math_indexnow' !== $doaction || empty( $object_ids ) ) {
			return $redirect;
		}

		if ( ! Helper::has_cap( 'general' ) ) {
			return $redirect;
		}

		$urls = [];
		foreach ( $object_ids as $object_id ) {
			$urls[] = get_permalink( $object_id );
		}

		$this->api_submit( $urls, true );

		return $redirect;
	}

	/**
	 * Register admin page.
	 */
	public function register_instant_indexing_settings() {
		$tabs = [
			'url-submission' => [
				'icon'    => 'rm-icon rm-icon-instant-indexing',
				'title'   => esc_html__( 'Submit URLs', 'rank-math' ),
				'desc'    => esc_html__( 'Send URLs directly to the IndexNow API.', 'rank-math' ) . ' <a href="' . KB::get( 'instant-indexing', 'Indexing Submit URLs' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>',
				'classes' => 'rank-math-advanced-option',
				'file'    => __DIR__ . '/views/console.php',
			],
			'settings'       => [
				'icon'  => 'rm-icon rm-icon-settings',
				'title' => esc_html__( 'Settings', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'  => sprintf( esc_html__( 'Instant Indexing module settings. %s.', 'rank-math' ), '<a href="' . KB::get( 'instant-indexing', 'Indexing Settings' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'file'  => __DIR__ . '/views/options.php',
			],
			'history'        => [
				'icon'    => 'rm-icon rm-icon-htaccess',
				'title'   => esc_html__( 'History', 'rank-math' ),
				'desc'    => esc_html__( 'The last 100 IndexNow API requests.', 'rank-math' ),
				'classes' => 'rank-math-advanced-option',
				'file'    => __DIR__ . '/views/history.php',
			],
		];

		if ( 'easy' === Helper::get_settings( 'general.setup_mode', 'advanced' ) ) {
			// Move ['settings'] to the top.
			$tabs = [ 'settings' => $tabs['settings'] ] + $tabs;
		}

		/**
		 * Allow developers to add new sections in the IndexNow settings.
		 *
		 * @param array $tabs
		 */
		$tabs = $this->do_filter( 'settings/instant_indexing', $tabs );

		new Register_Options_Page(
			[
				'key'        => 'rank-math-options-instant-indexing',
				'title'      => esc_html__( 'Instant Indexing', 'rank-math' ),
				'menu_title' => esc_html__( 'Instant Indexing', 'rank-math' ),
				'capability' => 'rank_math_general',
				'tabs'       => $tabs,
				'position'   => 11,
			]
		);
	}

	/**
	 * Store previous post status & permalink before saving the post.
	 *
	 * @param  array $data                Post data.
	 * @param  array $postarr             Raw post data.
	 * @param  array $unsanitized_postarr Unsanitized post data.
	 * @param  bool  $update              Whether this is an existing post being updated or not.
	 */
	public function before_save_post( $data, $postarr, $unsanitized_postarr, $update = false ) {
		if ( ! $update ) {
			return $data;
		}

		$this->previous_post_status[ $postarr['ID'] ]     = get_post_status( $postarr['ID'] );
		$this->previous_post_permalinks[ $postarr['ID'] ] = str_replace( '__trashed', '', get_permalink( $postarr['ID'] ) );

		return $data;
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
		// Check if already submitted.
		if ( in_array( $post_id, $this->submitted, true ) ) {
			return;
		}

		// Check if post status changed to publish or trash.
		if ( ! in_array( $post->post_status, [ 'publish', 'trash' ], true ) ) {
			return;
		}

		// If new status is trash, check if previous status was publish.
		if ( 'trash' === $post->post_status && 'publish' !== $this->previous_post_status[ $post_id ] ) {
			return;
		}

		if ( wp_is_post_revision( $post_id ) || wp_is_post_autosave( $post_id ) || ! empty( Helper::get_post_meta( 'lock_modified_date', $post_id ) ) ) {
			return;
		}

		if ( ! Helper::is_post_indexable( $post_id ) ) {
			return;
		}

		// Check if it's a hidden product.
		if ( 'product' === $post->post_type && Helper::is_woocommerce_active() ) {
			$product = wc_get_product( $post_id );
			if ( $product && ! $product->is_visible() ) {
				return;
			}
		}

		Sitepress::get()->remove_home_url_filter();
		$url = get_permalink( $post );
		if ( 'trash' === $post->post_status ) {
			$url = $this->previous_post_permalinks[ $post_id ];
		}
		Sitepress::get()->restore_home_url_filter();

		/**
		 * Filter the URL to be submitted to IndexNow.
		 * Returning false will prevent the URL from being submitted.
		 *
		 * @param string  $url  URL to be submitted.
		 * @param WP_POST $post Post object.
		 */
		$send_url = $this->do_filter( 'instant_indexing/publish_url', $url, $post );

		// Early exit if filter is set to false.
		if ( ! $send_url ) {
			return;
		}

		$this->api_submit( $send_url, false );
		$this->submitted[] = $post_id;
	}

	/**
	 * Is module configured.
	 *
	 * @return boolean
	 */
	private function is_configured() {
		return (bool) Helper::get_settings( 'instant_indexing.indexnow_api_key' );
	}

	/**
	 * Serve API key for search engines.
	 */
	public function serve_api_key() {
		global $wp;

		$api          = Api::get();
		$key          = $api->get_key();
		$key_location = $api->get_key_location( 'serve_api_key' );
		$current_url  = home_url( $wp->request );

		if ( isset( $current_url ) && $key_location === $current_url ) {
			header( 'Content-Type: text/plain' );
			header( 'X-Robots-Tag: noindex' );
			status_header( 200 );
			echo esc_html( $key );

			exit();
		}
	}

	/**
	 * Submit URL to IndexNow API.
	 *
	 * @param string $url                  URL to be submitted.
	 * @param bool   $is_manual_submission Whether the URL is submitted manually by the user.
	 *
	 * @return bool
	 */
	private function api_submit( $url, $is_manual_submission ) {
		$api = Api::get();

		/**
		 * Filter the URL to be submitted to IndexNow.
		 * Returning false will prevent the URL from being submitted.
		 *
		 * @param bool   $is_manual_submission Whether the URL is submitted manually by the user.
		 */
		$url = $this->do_filter( 'instant_indexing/submit_url', $url, $is_manual_submission );
		if ( ! $url ) {
			return false;
		}

		$api_logs = $api->get_log();
		if ( ! $is_manual_submission && ! empty( $api_logs ) ) {
			$logs = array_values( array_reverse( $api_logs ) );
			if ( ! empty( $logs[0] ) && $logs[0]['url'] === $url && time() - $logs[0]['time'] < self::THROTTLE_LIMIT ) {
				return false;
			}
		}

		$submitted = $api->submit( $url, $is_manual_submission );

		if ( ! $is_manual_submission ) {
			return $submitted;
		}

		$count = is_array( $url ) ? count( $url ) : 1;
		$this->add_submit_message_notice( $submitted, $count );

		return $submitted;
	}

	/**
	 * Add notice after submitting one or more URLs.
	 *
	 * @param bool $success Whether the submission was successful.
	 * @param int  $count   Number of submitted URLs.
	 *
	 * @return void
	 */
	private function add_submit_message_notice( $success, $count ) {
		$notification_type    = 'error';
		$notification_message = __( 'Error submitting page to IndexNow.', 'rank-math' );

		if ( $success ) {
			$notification_type    = 'success';
			$notification_message = sprintf(
				/* translators: %s: Number of pages submitted. */
				_n( '%s page submitted to IndexNow.', '%s pages submitted to IndexNow.', $count, 'rank-math' ),
				$count
			);
		}

		Helper::add_notification( $notification_message, [ 'type' => $notification_type ] );
	}

	/**
	 * Get post types where auto-submit is enabled.
	 *
	 * @return array
	 */
	private function get_auto_submit_post_types() {
		$post_types = Helper::get_settings( 'instant_indexing.bing_post_types', [] );
		return $post_types;
	}
}
