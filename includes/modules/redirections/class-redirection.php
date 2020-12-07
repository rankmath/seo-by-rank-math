<?php
/**
 * The Redirection Item.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Helper;
use RankMath\Helpers\Sitepress;
use MyThemeShop\Helpers\Url;

defined( 'ABSPATH' ) || exit;

/**
 * Redirection class.
 */
class Redirection {

	/**
	 * Hold redirection data.
	 *
	 * @var array
	 */
	private $data;

	/**
	 * Hold cache data.
	 *
	 * @var array
	 */
	private $cache;

	/**
	 * No pre redirection cache.
	 *
	 * @var bool
	 */
	private $nocache = false;

	/**
	 * Hold current parent domain.
	 *
	 * @var string
	 */
	private $domain = null;

	/**
	 * Hold state.
	 *
	 * @var string
	 */
	private $is_new = true;

	/**
	 * Retrieve Redirection instance.
	 *
	 * @param integer $id Redirection ID.
	 *
	 * @return Redirection
	 */
	public static function create( $id = 0 ) {
		$data = [
			'id'          => 0,
			'sources'     => [],
			'url_to'      => '',
			'header_code' => '301',
			'hits'        => '0',
			'status'      => 'active',
			'created'     => '',
			'updated'     => '',
		];

		if ( $id > 0 && $object = DB::get_redirection_by_id( $id ) ) { // phpcs:ignore
			$object['id'] = absint( $object['id'] );
			unset( $object['last_accessed'] );
			$data = $object;
		}

		return new self( $data );
	}

	/**
	 * Create instance from array.
	 *
	 * @param array $data Array of data.
	 *
	 * @return Redirection
	 */
	public static function from( $data ) {
		$sources = [];
		if ( isset( $data['sources'] ) ) {
			$sources = $data['sources'];
			unset( $data['sources'] );
		}

		$object = new self( $data );
		$object->add_sources( $sources );

		if ( isset( $data['url_to'] ) ) {
			$object->add_destination( $data['url_to'] );
		}

		return $object;
	}

	/**
	 * Constructor.
	 *
	 * @param array $data    Array of item data.
	 * @param bool  $nocache Don't do pre-cache.
	 */
	public function __construct( $data, $nocache = false ) {
		$this->data    = $data;
		$this->nocache = $nocache;

		if ( isset( $data['id'] ) && $data['id'] > 0 ) {
			$this->is_new = false;
		}
	}

	/**
	 * Getter.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param string $key Key to get.
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		if ( isset( $this->data[ $key ] ) ) {
			return $this->data[ $key ];
		}

		return $this->$key;
	}

	/**
	 * Get item ID.
	 *
	 * @return int
	 */
	public function get_id() {
		return $this->id;
	}

	/**
	 * Set item ID.
	 *
	 * @param int $id Item ID.
	 */
	public function set_id( $id ) {
		$this->data['id'] = $id;
	}

	/**
	 * Set cache setting.
	 *
	 * @codeCoverageIgnore
	 *
	 * @param bool $nocache Can save cache or not.
	 */
	public function set_nocache( $nocache ) {
		$this->nocache = $nocache;
	}

	/**
	 * Is new redirection.
	 *
	 * @return int
	 */
	public function is_new() {
		return $this->is_new;
	}

	/**
	 * Has sources.
	 *
	 * @return bool
	 */
	public function has_sources() {
		return ! empty( $this->data['sources'] ) && is_array( $this->data['sources'] );
	}

	/**
	 * Save to database.
	 */
	public function save() {
		if ( false === $this->has_sources() ) {
			return false;
		}

		$this->set_id( DB::update_iff( $this->data ) );

		if ( false === $this->nocache ) {
			$this->save_redirection_cache();
		}

		return $this->get_id();
	}

	/**
	 * Add sources.
	 *
	 * @param array $sources Sources to add.
	 */
	public function add_sources( $sources ) {
		foreach ( $sources as $key => $value ) {
			$value['comparison'] = empty( $value['comparison'] ) ? 'exact' : $value['comparison'];
			$this->add_source( $value['pattern'], $value['comparison'], ! empty( $value['ignore'] ) ? 'case' : '' );
		}
	}

	/**
	 * Add source.
	 *
	 * @param string $pattern    Pattern to add.
	 * @param string $comparison Comparison for pattern.
	 * @param string $ignore     Ignore flag.
	 */
	public function add_source( $pattern, $comparison, $ignore = '' ) {
		$pattern = trim( $pattern );
		if ( empty( $pattern ) ) {
			return;
		}

		$pattern = $this->sanitize_source( wp_strip_all_tags( $pattern, true ), $comparison );
		if ( ! $pattern ) {
			return;
		}

		$this->data['sources'][] = [
			'ignore'     => $ignore,
			'pattern'    => $pattern,
			'comparison' => $comparison,
		];
	}

	/**
	 * Add and sanitize destination URL.
	 *
	 * @param string $url URL to process.
	 */
	public function add_destination( $url ) {
		$processed = trim( wp_strip_all_tags( $url, true ) );

		// If beginning looks like a domain but without protocol then let's add home_url().
		if ( ! empty( $processed ) && Url::is_relative( $processed ) ) {
			$processed = home_url( $processed );
		}

		$this->data['url_to'] = $processed;
	}

	/**
	 * Sanitize source.
	 *
	 * @param string $pattern    Pattern to sanitize.
	 * @param string $comparison Comparison of pattern.
	 *
	 * @return string
	 */
	private function sanitize_source( $pattern, $comparison ) {
		if ( 'regex' === $comparison ) {
			return $this->sanitize_source_regex( $pattern );
		}

		$pattern = $this->sanitize_source_url( $pattern );
		if ( $pattern && 'exact' === $comparison && false === $this->nocache ) {
			$this->pre_redirection_cache( $pattern );
		}

		return $pattern;
	}

	/**
	 * Sanitize redirection source URL.
	 *
	 * Following urls converted to URI:
	 *    '' => false
	 *    '/' => false
	 *    /URI => URI
	 *    #URI => #URI
	 *    https://website.com/#URI/ => #URI
	 *    https://website.com#URI/ => #URI
	 *    website.com => false
	 *    www.website.com => false
	 *    http://sub.website.com/URI => false
	 *    http://external.com/URI => false
	 *    website.com/URI => URI
	 *    website.com/URI/ => URI
	 *    http://website.com/URI => URI
	 *    http://website.com/URI/ => URI
	 *    https://website.com/URI => URI
	 *    https://website.com/URI/ => URI
	 *    www.website.com/URI => URI
	 *    www.website.com/URI/ => URI
	 *    http://www.website.com/URI => URI
	 *    http://www.website.com/URI/ => URI
	 *    https://www.website.com/URI => URI
	 *    https://www.website.com/URI/ => URI
	 *
	 * @param string $url User-input source URL.
	 *
	 * @return string|false
	 */
	private function sanitize_source_url( $url ) {
		if ( empty( $url ) || '/' === $url ) {
			return false;
		}

		if ( '#' === $url[0] || '/' === $url[0] ) {
			return ltrim( $url, '/' );
		}

		$domain = $this->get_home_domain();
		$url    = trailingslashit( $url );
		$url    = str_replace( $domain . '#', $domain . '/#', $url ); // For website.com#URI link.
		$domain = trailingslashit( $domain );
		$search = [
			'http://' . $domain,
			'http://www.' . $domain,
			'https://' . $domain,
			'https://www.' . $domain,
			'www.' . $domain,
		];
		$url    = str_replace( $search, '', $url );
		$url    = preg_replace( '/^' . preg_quote( $domain, '/' ) . '/s', '', $url );

		// Empty url.
		// External domain.
		if ( empty( $url ) || 0 === strpos( $url, 'http://' ) || 0 === strpos( $url, 'https://' ) ) {
			return false;
		}

		return urldecode( untrailingslashit( self::strip_subdirectory( $url ) ) );
	}

	/**
	 * Sanitize redirection source for regex.
	 *
	 * @param  string $pattern Pattern to process.
	 * @return string
	 */
	private function sanitize_source_regex( $pattern ) {
		// No new lines.
		$pattern = preg_replace( "/[\r\n\t].*?$/s", '', $pattern );

		// Clean control codes.
		$pattern = preg_replace( '/[^\PC\s]/u', '', $pattern );

		// Check if it's a valid pattern.
		if ( @preg_match( '@' . $pattern . '@', '' ) === false ) { // phpcs:ignore
			/* translators: source pattern */
			Helper::add_notification( sprintf( __( 'Invalid regex pattern: %s', 'rank-math' ), $pattern ), [ 'type' => 'error' ] );
			return false;
		}

		return $pattern;
	}

	/**
	 * Maybe collect WordPress object to add redirection cache.
	 *
	 * @param string $slug Url to search for.
	 */
	private function pre_redirection_cache( $slug ) {
		global $wpdb;

		// Check for post.
		$post_id = url_to_postid( home_url( $slug ) );
		if ( $post_id ) {
			$this->cache[] = [
				'from_url'    => $slug,
				'object_id'   => $post_id,
				'object_type' => 'post',
			];
			return;
		}

		// Check for term.
		$terms = $wpdb->get_results( $wpdb->prepare( "SELECT term_id FROM $wpdb->terms WHERE slug = %s", $slug ) );
		if ( $terms ) {
			foreach ( $terms as $term ) {
				$this->cache[] = [
					'from_url'    => $slug,
					'object_id'   => $term->term_id,
					'object_type' => 'term',
				];
			}
			return;
		}

		// Check for user.
		$user = get_user_by( 'slug', $slug );
		if ( $user ) {
			$this->cache[] = [
				'from_url'    => $slug,
				'object_id'   => $user->ID,
				'object_type' => 'user',
			];
			return;
		}
	}

	/**
	 * Save redirection caches.
	 */
	private function save_redirection_cache() {
		if ( ! $this->get_id() || empty( $this->cache ) ) {
			return;
		}

		foreach ( $this->cache as $item ) {
			$item['redirection_id'] = $this->get_id();
			Cache::add( $item );
		}
	}

	/**
	 * Get the domain, without www. and protocol.
	 *
	 * @return string
	 */
	private function get_home_domain() {
		if ( ! is_null( $this->domain ) ) {
			return $this->domain;
		}

		$this->domain = Url::get_domain( home_url() );

		return $this->domain;
	}

	/**
	 * Strip home directory when WP is installed in subdirectory
	 *
	 * @param string $url URL to strip from.
	 *
	 * @return string
	 */
	public static function strip_subdirectory( $url ) {
		Sitepress::get()->remove_home_url_filter();
		$home_dir = ltrim( home_url( '', 'relative' ), '/' );
		Sitepress::get()->restore_home_url_filter();

		return $home_dir ? str_replace( trailingslashit( $home_dir ), '', $url ) : $url;
	}
}
