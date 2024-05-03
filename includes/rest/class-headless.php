<?php
/**
 * Add support for headless WP.
 *
 * @since      1.0.15
 * @package    RankMath
 * @subpackage RankMath\Rest
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Rest;

use WP_Error;
use WP_REST_Server;
use WP_REST_Request;
use WP_REST_Response;
use WP_REST_Controller;
use RankMath\Helpers\Url;
use RankMath\Helper;
use RankMath\Frontend\Frontend;

defined( 'ABSPATH' ) || exit;

/**
 * Front class.
 */
class Headless extends WP_REST_Controller {

	/**
	 * Whether the request is for the homepage.
	 *
	 * @var boolean
	 */
	public $is_home = false;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->namespace = \RankMath\Rest\Rest_Helper::BASE;
	}

	/**
	 * Registers the routes for the objects of the controller.
	 */
	public function register_routes() {
		if ( ! Helper::get_settings( 'general.headless_support' ) ) {
			return;
		}

		register_rest_route(
			$this->namespace,
			'/getHead',
			[
				'methods'             => WP_REST_Server::READABLE,
				'callback'            => [ $this, 'get_head' ],
				'permission_callback' => '__return_true',
				'args'                => [
					'url' => [
						'type'              => 'string',
						'required'          => true,
						'description'       => esc_html__( 'URL to get HTML tags for.', 'rank-math' ),
						'validate_callback' => [ $this, 'is_valid_url' ],
					],
				],
			]
		);
	}

	/**
	 * Get all tags that go in the <head>. Useful for headless WP installations.
	 *
	 * @param WP_REST_Request $request Request object, should include the "url" parameter.
	 *
	 * @return WP_REST_Response|WP_Error Response object on success, or WP_Error object on failure.
	 */
	public function get_head( WP_REST_Request $request ) {
		$resp = new WP_REST_Response();
		$url  = $request->get_param( 'url' );

		$html = $this->get_html_head( $url );

		$resp->set_status( 200 );
		$resp->set_data(
			[
				'success' => true,
				'head'    => $html,

			]
		);
		return $resp;
	}

	/**
	 * Return Rank Math head HTML output for the given URL.
	 *
	 * @param  string $url Request URL.
	 *
	 * @return string
	 */
	private function get_html_head( $url ) {
		$this->setup_post_head( $url );

		ob_start();
		do_action( 'wp' );
		do_action( 'rank_math/head' );
		return ob_get_clean();
	}

	/**
	 * Prepare head output for a URL.
	 *
	 * @param string $url Request URL.
	 *
	 * @return void
	 */
	private function setup_post_head( $url ) {
		// Setup WordPress.
		$_SERVER['REQUEST_URI'] = $this->generate_request_uri( $url );
		remove_all_actions( 'wp' );
		remove_all_actions( 'parse_request' );
		wp();

		if ( $this->is_home ) {
			$GLOBALS['wp_query']->is_home = true;
		}

		remove_filter( 'option_rewrite_rules', [ $this, 'fix_query_notice' ] );
		header( 'Content-Type: application/json; charset=UTF-8' );

		// Setup Rank Math.
		rank_math()->variables->setup();
		rank_math()->manager->load_modules();
		new Frontend();
	}

	/**
	 * Generate $_SERVER['REQUEST_URI'] value based on input URL.
	 *
	 * @param string $url Input URL.
	 * @return string
	 */
	public function generate_request_uri( $url ) {
		$quoted      = preg_quote( rtrim( home_url(), '/' ), '/' );
		$request_uri = preg_replace( sprintf( '/^%s/i', $quoted ), '', rtrim( $url, '/' ) );
		if ( empty( $request_uri ) ) {
			$request_uri   = '/';
			$this->is_home = true;
			$front_page_id = get_option( 'page_on_front' );
			if ( 'page' === get_option( 'show_on_front' ) && $front_page_id ) {
				$this->is_home = false;
				$request_uri   = get_post_field( 'post_name', $front_page_id );
			}

			add_filter( 'option_rewrite_rules', [ $this, 'fix_query_notice' ] );
		}

		return $request_uri;
	}

	/**
	 * Filter rewrite_rules to avoid a PHP notice.
	 *
	 * @param array $rules Original rules.
	 * @return array
	 */
	public function fix_query_notice( $rules ) {
		if ( ! is_array( $rules ) || isset( $rules['$'] ) ) {
			return $rules;
		}

		global $wp_rewrite;
		$rules['$'] = $wp_rewrite->index;
		return $rules;
	}

	/**
	 * Check if provided URL is valid and internal.
	 *
	 * @param string $url URL.
	 *
	 * @return boolean
	 */
	public function is_valid_url( $url ) {
		$url = preg_replace_callback(
			'/[^\x20-\x7f]/',
			function( $match ) {
				return rawurlencode( $match[0] );
			},
			$url
		);

		return Url::is_url( $url ) && ! Url::is_external( $url );
	}
}
