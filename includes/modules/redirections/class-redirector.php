<?php
/**
 * The Redirector.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use WP_Query;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Redirector class.
 */
class Redirector {

	use Hooker;

	/**
	 * Matched redirection.
	 *
	 * @var array
	 */
	private $matched = false;

	/**
	 * Redirect to this URL.
	 *
	 * @var string
	 */
	private $redirect_to;

	/**
	 * Current request URI.
	 *
	 * @var string
	 */
	private $uri = '';

	/**
	 * Current request uri with querystring.
	 *
	 * @var string
	 */
	private $full_uri = '';

	/**
	 * Current query string.
	 *
	 * @var string
	 */
	private $query_string = '';

	/**
	 * From cache.
	 *
	 * @var bool
	 */
	private $cache = false;

	/**
	 * Sets the error template to include.
	 *
	 * @var string
	 */
	protected $template_file_path;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->start();
		$this->flow();
		$this->redirect();
	}

	/**
	 * Set the required values.
	 */
	private function start() {
		// Complete request uri.
		$this->full_uri = Redirection::get_full_uri();

		// Remove query string.
		$this->uri = explode( '?', $this->full_uri );
		if ( isset( $this->uri[1] ) ) {
			$this->query_string = $this->uri[1];
		}
		$this->uri = trim( $this->uri[0], '/' );

		if ( $this->is_amp_endpoint() ) {
			$this->uri = \str_replace( '/' . amp_get_slug(), '', $this->uri );
		}
	}

	/**
	 * Run the system flow.
	 */
	private function flow() {
		$flow = [ 'pre_filter', 'from_cache', 'everything', 'fallback' ];
		foreach ( $flow as $func ) {
			if ( false !== $this->matched ) {
				break;
			}

			$this->$func();
		}
	}

	/**
	 * If we got a match, redirect.
	 */
	private function redirect() {
		if ( false === $this->matched ) {
			return;
		}

		if ( isset( $this->matched['id'], $this->matched['url_to'] ) ) {
			DB::update_access( $this->matched );
		}

		$header_code = $this->get_header_code();
		if ( in_array( $header_code, [ 410, 451 ], true ) ) {
			$this->redirect_without_target( $header_code );
			return;
		}

		$this->do_debugging();

		if ( true === $this->do_filter( 'redirection/add_query_string', true, $this->matched ) && Str::is_non_empty( $this->query_string ) ) {
			$this->redirect_to .= '?' . $this->query_string;
		}

		if ( wp_redirect( esc_url_raw( $this->redirect_to ), $header_code, $this->get_redirect_header() ) ) { // phpcs:ignore
			exit;
		}
	}

	/**
	 * Handles the redirects without a target by setting the needed hooks.
	 *
	 * @param string $header_code The type of the redirect.
	 *
	 * @return void
	 */
	private function redirect_without_target( $header_code ) {
		$has_include_hook = $this->set_template_include_hook( $header_code );
		if ( ! $has_include_hook ) {
			$this->set_404();
		}

		if ( 410 === $header_code ) {
			status_header( 410 );
		}

		if ( 451 === $header_code ) {
			status_header( 451, 'Unavailable For Legal Reasons' );
		}
	}

	/**
	 * Sets the hook for setting the template include. This is the file that we want to show.
	 *
	 * @param string $template The template to look for.
	 *
	 * @return bool True when template should be included.
	 */
	protected function set_template_include_hook( $template ) {
		$this->template_file_path = get_query_template( $template );
		if ( ! empty( $this->template_file_path ) ) {
			$this->filter( 'template_include', 'set_template_include' );
			return true;
		}

		return false;
	}

	/**
	 * Returns the template that should be included.
	 *
	 * @param string $template The template that will included before executing hook.
	 *
	 * @return string Returns the template that should be included.
	 */
	public function set_template_include( $template ) {
		if ( ! empty( $this->template_file_path ) ) {
			return $this->template_file_path;
		}

		return $template;
	}

	/**
	 * Pre filter
	 */
	private function pre_filter() {
		$pre = $this->do_filter(
			'redirection/pre_search',
			null,
			$this->uri,
			$this->full_uri
		);

		if ( null === $pre || ! is_array( $pre ) ) {
			return;
		}

		$this->matched     = $pre;
		$this->redirect_to = $pre['url_to'];
	}

	/**
	 * Search from cache.
	 */
	private function from_cache() {
		$redirections = Cache::get_by_object_id_or_url( (int) get_queried_object_id(), $this->get_current_object_type(), $this->uri );
		foreach ( $redirections as $redirection ) {
			if ( empty( $redirection->object_id ) ) {
				$this->cache = true;
				$this->set_redirection( $redirection->redirection_id );
				return;
			}

			if ( trim( $redirection->from_url, '/' ) === $this->uri ) {
				$this->cache = true;
				$this->set_redirection( $redirection->redirection_id );
				return;
			}
		}
	}

	/**
	 * Search for everything rules.
	 */
	private function everything() {
		$redirection = DB::match_redirections( $this->uri );
		if ( ! $redirection && $this->uri !== $this->full_uri ) {
			$redirection = DB::match_redirections( $this->full_uri );
		}

		if ( $redirection ) {
			Cache::add(
				[
					'from_url'       => $this->uri,
					'redirection_id' => $redirection['id'],
					'object_id'      => 0,
					'object_type'    => 'any',
					'is_redirected'  => '1',
				]
			);
			$this->set_redirection( $redirection );
		}
	}

	/**
	 * Do the fallback strategy here.
	 */
	private function fallback() {
		if ( ! $this->can_run_fallback() ) {
			return;
		}

		$behavior = Helper::get_settings( 'general.redirections_fallback' );
		if ( 'default' === $behavior ) {
			return;
		}

		if ( 'homepage' === $behavior ) {
			$this->matched     = [];
			$this->redirect_to = home_url();
			return;
		}

		$custom_url = Helper::get_settings( 'general.redirections_custom_url' );
		if ( ! empty( $custom_url ) ) {
			$this->matched     = [];
			$this->redirect_to = $custom_url;
		}
	}

	/**
	 * Show debugging interstitial if enabled.
	 */
	private function do_debugging() {
		if ( ! Helper::get_settings( 'general.redirections_debug' ) || ! Helper::has_cap( 'redirections' ) ) {
			return;
		}

		new Debugger( get_object_vars( $this ) );
	}

	/**
	 * Set redirection by ID.
	 *
	 * @param integer $redirection Redirection ID to set for.
	 */
	private function set_redirection( $redirection ) {
		if ( ! is_array( $redirection ) ) {
			$redirection = DB::get_redirection_by_id( $redirection, 'active' );
		}

		$custom_match = $this->do_filter( 'redirection/redirection_match', false, $redirection );
		if ( false === $redirection || ( ! DB::compare_sources( $redirection['sources'], $this->uri ) && ! $custom_match ) ) {
			return;
		}

		if ( isset( $redirection['url_to'] ) ) {
			$this->matched = $redirection;
			$this->set_redirect_to();
		}

		if ( $this->is_amp_endpoint() ) {
			$this->redirect_to = $this->redirect_to . amp_get_slug() . '/';
		}
	}

	/**
	 * Set redirect to.
	 */
	private function set_redirect_to() {
		$this->redirect_to = $this->matched['url_to'];
		foreach ( $this->matched['sources'] as $source ) {
			$this->set_redirect_to_regex( $source );
		}
	}

	/**
	 * Set redirect to by replacing using regex.
	 *
	 * @param array $source Source to check.
	 */
	private function set_redirect_to_regex( $source ) {
		if ( 'regex' !== $source['comparison'] ) {
			return;
		}

		$pattern = DB::get_clean_pattern( $source['pattern'], $source['comparison'] );
		if ( Str::comparison( $pattern, $this->uri, $source['comparison'] ) ) {
			$this->redirect_to = preg_replace( $pattern, $this->redirect_to, $this->uri );
		}
	}

	/**
	 * Sets the wp_query to 404 when this is an object.
	 */
	private function set_404() {
		global $wp_query;

		$wp_query         = is_object( $wp_query ) ? $wp_query : new WP_Query();
		$wp_query->is_404 = true;
	}

	/**
	 * Get the object type for the current page.
	 *
	 * @return string object type name.
	 */
	private function get_current_object_type() {
		$hash   = [
			'WP_Post' => 'post',
			'WP_Term' => 'term',
			'WP_User' => 'user',
		];
		$object = get_queried_object();
		if ( ! $object ) {
			return 'none';
		}

		$object = get_class( $object );
		return isset( $hash[ $object ] ) ? $hash[ $object ] : 'none';
	}

	/**
	 * Get header code.
	 *    1. From matched redirection.
	 *    2. From optgeneral options.
	 *
	 * @return int
	 */
	private function get_header_code() {
		$header_code = isset( $this->matched['header_code'] ) ? $this->matched['header_code'] : Helper::get_settings( 'general.redirections_header_code' );
		return absint( $header_code );
	}

	/**
	 * Get redirect header.
	 *
	 * @return string
	 */
	private function get_redirect_header() {
		return true === $this->do_filter( 'redirection/add_redirect_header', true ) ? 'Rank Math' : 'WordPress';
	}

	/**
	 * Is AMP url.
	 *
	 * @return bool
	 */
	private function is_amp_endpoint() {
		return \function_exists( 'is_amp_endpoint' ) && \function_exists( 'amp_is_canonical' ) && is_amp_endpoint() && ! amp_is_canonical();
	}

	/**
	 * Gets the post id for the redirections' fallback.
	 *
	 * @return int|void
	 */
	private static function get_redirections_fallback_post_id() {
		$fall_back = Helper::get_settings( 'general.redirections_fallback' );

		if ( in_array( $fall_back, [ 'default', 'homepage' ], true ) ) {
			return (int) get_option( 'page_on_front' );
		}

		if ( Helper::get_settings( 'general.redirections_custom_url' ) ) {
			return url_to_postid( Helper::get_settings( 'general.redirections_custom_url' ) );
		}
	}

	/**
	 * Check if the fall_back redirect can run in the current contexts.
	 *
	 * @return bool
	 */
	private function can_run_fallback() {
		if ( ! is_404() ) {
			return false;
		}

		if ( ! $this->uri && $this->query_string && Str::starts_with( 'p=', trim( $this->query_string ) ) ) {
			$this->query_string = '';
			return true;
		}

		$wp_redirect_admin_locations = $this->do_filter( 'redirection/fallback_exclude_locations', [ 'login', 'admin', 'dashboard' ] );
		return $this->uri && ! in_array( $this->uri, $wp_redirect_admin_locations, true );
	}
}
