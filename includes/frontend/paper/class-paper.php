<?php
/**
 * The Paper Class
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\Post;
use RankMath\Helper;
use RankMath\Sitemap\Router;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use RankMath\Helpers\Security;

defined( 'ABSPATH' ) || exit;

/**
 * Paper class.
 */
class Paper {

	use Hooker;

	/**
	 * Hold the class instance.
	 *
	 * @var object
	 */
	private static $instance = null;

	/**
	 * Hold current paper object.
	 *
	 * @var IPaper
	 */
	private $paper = null;

	/**
	 * Hold title.
	 *
	 * @var string
	 */
	private $title = null;

	/**
	 * Hold description.
	 *
	 * @var string
	 */
	private $description = null;

	/**
	 * Hold robots.
	 *
	 * @var array
	 */
	private $robots = null;

	/**
	 * Hold canonical.
	 *
	 * @var array
	 */
	private $canonical = null;

	/**
	 * Hold keywords.
	 *
	 * @var string
	 */
	private $keywords = null;

	/**
	 * Initialize object
	 *
	 * @return object Post|Term|User.
	 */
	public static function get() {
		if ( ! is_null( self::$instance ) ) {
			return self::$instance;
		}

		self::$instance = new Paper();
		self::$instance->setup();
		return self::$instance;
	}

	/**
	 * Setup paper.
	 */
	private function setup() {
		foreach ( $this->get_papers() as $class_name => $is_valid ) {
			if ( $this->do_filter( 'paper/is_valid/' . strtolower( $class_name ), $is_valid ) ) {
				$class_name  = '\\RankMath\\Paper\\' . $class_name;
				$this->paper = new $class_name();
				break;
			}
		}

		if ( ! method_exists( $this->paper, 'set_object' ) ) {
			return;
		}

		if ( Post::is_home_static_page() ) {
			$this->paper->set_object( get_queried_object() );
		} elseif ( Post::is_simple_page() ) {
			$post = Post::get( Post::get_page_id() );
			$this->paper->set_object( $post->get_object() );
		}
	}

	/**
	 * Get papers types.
	 *
	 * @return array
	 */
	private function get_papers() {
		return $this->do_filter(
			'paper/hash',
			[
				'Search'    => is_search(),
				'Shop'      => Post::is_shop_page(),
				'Singular'  => Post::is_home_static_page() || Post::is_simple_page(),
				'Blog'      => Post::is_home_posts_page(),
				'Author'    => is_author() || ( Helper::is_module_active( 'bbpress' ) && function_exists( 'bbp_is_single_user' ) && bbp_is_single_user() ),
				'Date'      => is_date(),
				'Taxonomy'  => is_category() || is_tag() || is_tax(),
				'Archive'   => is_archive(),
				'Error_404' => is_404(),
				'Misc'      => true,
			]
		);
	}

	/**
	 * Get title after sanitization.
	 *
	 * @return string
	 */
	public function get_title() {
		if ( ! is_null( $this->title ) ) {
			return $this->title;
		}

		/**
		 * Allow changing the title.
		 *
		 * @param string $title The page title being put out.
		 */
		$this->title = $this->do_filter( 'frontend/title', $this->paper->title() );

		// Early Bail!!
		if ( '' === $this->title ) {
			return $this->title;
		}

		// Remove excess whitespace.
		$this->title = preg_replace( '[\s\s+]', ' ', $this->title );

		// Capitalize Titles.
		if ( Helper::get_settings( 'titles.capitalize_titles' ) ) {
			$this->title = ucwords( $this->title );
		}

		$this->title = wp_strip_all_tags( stripslashes( $this->title ), true );
		$this->title = esc_html( $this->title );
		$this->title = convert_smilies( $this->title );

		return $this->title;
	}

	/**
	 * Get description after sanitization.
	 *
	 * @return string
	 */
	public function get_description() {
		if ( ! is_null( $this->description ) ) {
			return $this->description;
		}

		/**
		* Allow changing the meta description sentence.
		*
		* @param string $description The description sentence.
		*/
		$this->description = $this->do_filter( 'frontend/description', trim( $this->paper->description() ) );

		// Early Bail!!
		if ( '' === $this->description ) {
			return $this->description;
		}

		$this->description = wp_strip_all_tags( stripslashes( $this->description ), true );
		$this->description = esc_attr( $this->description );

		return $this->description;
	}

	/**
	 * Get robots after sanitization.
	 *
	 * @return array
	 */
	public function get_robots() {
		if ( ! is_null( $this->robots ) ) {
			return $this->robots;
		}

		$this->robots = $this->paper->robots();
		if ( empty( $this->robots ) ) {
			$this->robots = self::robots_combine( Helper::get_settings( 'titles.robots_global' ) );
		}
		$this->validate_robots();
		$this->respect_settings_for_robots();

		/**
		 * Allows filtering of the meta robots.
		 *
		 * @param array $robots The meta robots directives to be echoed.
		 */
		$this->robots = $this->do_filter( 'frontend/robots', array_unique( $this->robots ) );
		$this->advanced_robots();

		return $this->robots;
	}

	/**
	 * Validate robots.
	 */
	private function validate_robots() {
		if ( empty( $this->robots ) || ! is_array( $this->robots ) ) {
			$this->robots = [
				'index'  => 'index',
				'follow' => 'follow',
			];
			return;
		}

		$this->robots = array_intersect_key(
			$this->robots,
			[
				'index'        => '',
				'follow'       => '',
				'noarchive'    => '',
				'noimageindex' => '',
				'nosnippet'    => '',
			]
		);

		// Add Index and Follow.
		if ( ! isset( $this->robots['index'] ) ) {
			$this->robots = [ 'index' => 'index' ] + $this->robots;
		}
		if ( ! isset( $this->robots['follow'] ) ) {
			$this->robots = [ 'follow' => 'follow' ] + $this->robots;
		}
	}

	/**
	 * Add Advanced robots.
	 */
	private function advanced_robots() {
		// Early Bail if robots is set to noindex or nosnippet!
		if ( ( isset( $this->robots['index'] ) && 'noindex' === $this->robots['index'] ) || ( isset( $this->robots['nosnippet'] ) && 'nosnippet' === $this->robots['nosnippet'] ) ) {
			return;
		}

		$advanced_robots = $this->paper->advanced_robots();
		if ( ! is_array( $advanced_robots ) ) {
			$advanced_robots = wp_parse_args(
				Helper::get_settings( 'titles.advanced_robots_global' ),
				[
					'max-snippet'       => -1,
					'max-video-preview' => -1,
					'max-image-preview' => 'large',
				]
			);

			$advanced_robots = self::advanced_robots_combine( $advanced_robots );
		}

		$advanced_robots = array_intersect_key(
			$advanced_robots,
			[
				'max-snippet'       => '',
				'max-video-preview' => '',
				'max-image-preview' => '',
			]
		);

		/**
		 * Allows filtering of the advanced meta robots.
		 *
		 * @param array $robots The meta robots directives to be echoed.
		 */
		$advanced_robots = $this->do_filter( 'frontend/advanced_robots', array_unique( $advanced_robots ) );

		$this->robots = ! empty( $advanced_robots ) ? $this->robots + $advanced_robots : $this->robots;
	}

	/**
	 * Get focus keywords
	 *
	 * @return string
	 */
	public function get_keywords() {
		if ( ! is_null( $this->keywords ) ) {
			return $this->keywords;
		}

		$this->keywords = $this->paper->keywords();

		/**
		 * Allows filtering of the meta keywords.
		 *
		 * @param array $keywords The meta keywords to be echoed.
		 */
		return $this->do_filter( 'frontend/keywords', $this->keywords );
	}

	/**
	 * Respect some robots settings.
	 */
	private function respect_settings_for_robots() {
		// If blog is not public or replytocom is set, then force noindex.
		if ( 0 === absint( get_option( 'blog_public' ) ) || isset( $_GET['replytocom'] ) ) {
			$this->robots['index']  = 'noindex';
			$this->robots['follow'] = 'nofollow';
		}

		// Force noindex for sub-pages.
		if ( is_paged() && Helper::get_settings( 'titles.noindex_archive_subpages' ) ) {
			$this->robots['index'] = 'noindex';
		}
	}

	/**
	 * Get canonical after sanitization.
	 *
	 * @param bool $un_paged    Whether or not to return the canonical with or without pagination added to the URL.
	 * @param bool $no_override Whether or not to return a manually overridden canonical.
	 *
	 * @return string
	 */
	public function get_canonical( $un_paged = false, $no_override = false ) {
		if ( is_null( $this->canonical ) ) {
			$this->generate_canonical();
		}

		$canonical = $this->canonical['canonical'];
		if ( $un_paged ) {
			$canonical = $this->canonical['canonical_unpaged'];
		} elseif ( $no_override ) {
			$canonical = $this->canonical['canonical_no_override'];
		}

		return $canonical;
	}

	/**
	 * Generate canonical URL parts.
	 */
	private function generate_canonical() {
		$this->canonical = wp_parse_args(
			$this->paper->canonical(),
			[
				'canonical'          => false,
				'canonical_unpaged'  => false,
				'canonical_override' => false,
			]
		);
		extract( $this->canonical ); // phpcs:ignore

		if ( is_front_page() || ( function_exists( 'ampforwp_is_front_page' ) && ampforwp_is_front_page() ) ) {
			$canonical = user_trailingslashit( home_url() );
		}

		// If not singular than we can have pagination.
		if ( ! is_singular() ) {
			$canonical_unpaged = $canonical;
			$canonical         = $this->get_canonical_paged( $canonical );
		}

		$this->canonical['canonical_unpaged']     = $canonical_unpaged;
		$this->canonical['canonical_no_override'] = $canonical;

		// Force absolute URLs for canonicals.
		$canonical = Str::is_non_empty( $canonical ) && true === Url::is_relative( $canonical ) ? $this->base_url( $canonical ) : $canonical;
		$canonical = Str::is_non_empty( $canonical_override ) ? $canonical_override : $canonical;

		/**
		 * Filter the canonical URL.
		 *
		 * @param string $canonical The canonical URL.
		 */
		$this->canonical['canonical'] = apply_filters( 'rank_math/frontend/canonical', $canonical );
	}

	/**
	 * Get the paged version of the canonical URL if needed.
	 *
	 * @param string $canonical The canonical URL.
	 *
	 * @return string
	 */
	private function get_canonical_paged( $canonical ) {
		global $wp_rewrite;

		if ( ! $canonical || get_query_var( 'paged' ) < 2 ) {
			return $canonical;
		}

		if ( ! $wp_rewrite->using_permalinks() ) {
			return Security::add_query_arg_raw(
				'paged',
				get_query_var( 'paged' ),
				is_front_page() ? trailingslashit( $canonical ) : $canonical
			);
		}

		return user_trailingslashit(
			trailingslashit( is_front_page() ? Router::get_base_url( '' ) : $canonical ) .
			trailingslashit( $wp_rewrite->pagination_base ) .
			get_query_var( 'paged' )
		);
	}

	/**
	 * Get the base URL for relative URLs by parsing the home URL.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast (https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param  string $path Optional path string.
	 * @return string
	 */
	private function base_url( $path = null ) {
		$parts    = wp_parse_url( get_option( 'home' ) );
		$base_url = trailingslashit( $parts['scheme'] . '://' . $parts['host'] );

		if ( ! is_null( $path ) ) {
			$base_url .= ltrim( $path, '/' );
		}

		return $base_url;
	}

	/**
	 * Get title or description option from the settings.
	 * The results will be run through the Helper::replace_vars() function.
	 *
	 * @param string       $id      Name of the option we are looking for.
	 * @param object|array $object  Object to pass to the replace_vars function.
	 * @param string       $default Default value if nothing found.
	 *
	 * @return string
	 */
	public static function get_from_options( $id, $object = [], $default = '' ) {
		$value = Helper::get_settings( "titles.$id" );

		// Break loop.
		if ( ! Str::ends_with( 'default_snippet_name', $value ) && ! Str::ends_with( 'default_snippet_desc', $value ) ) {
			$value = \str_replace(
				[ '%seo_title%', '%seo_description%' ],
				[ '%title%', '%excerpt%' ],
				$value
			);
		}

		return Helper::replace_vars( '' !== $value ? $value : $default, $object );
	}

	/**
	 * Make robots values as keyed array.
	 *
	 * @param array $robots  Main instance.
	 * @param bool  $default Append default.
	 *
	 * @return array
	 */
	public static function robots_combine( $robots, $default = false ) {
		if ( empty( $robots ) || ! is_array( $robots ) ) {
			return ! $default ? [] : [
				'index'  => 'index',
				'follow' => 'follow',
			];
		}

		$robots = array_combine( $robots, $robots );

		// Fix noindex key to index.
		if ( isset( $robots['noindex'] ) ) {
			$robots = [ 'index' => $robots['noindex'] ] + $robots;
			unset( $robots['noindex'] );
		}

		// Fix nofollow key to follow.
		if ( isset( $robots['nofollow'] ) ) {
			$robots = [ 'follow' => $robots['nofollow'] ] + $robots;
			unset( $robots['nofollow'] );
		}

		return $robots;
	}

	/**
	 * Make robots values as keyed array.
	 *
	 * @param array $advanced_robots  Main instance.
	 *
	 * @return array
	 */
	public static function advanced_robots_combine( $advanced_robots ) {
		if ( empty( $advanced_robots ) ) {
			return;
		}

		$robots = [];
		foreach ( $advanced_robots as $key => $data ) {
			if ( $data ) {
				$robots[ $key ] = $key . ':' . $data;
			}
		}
		return $robots;
	}

	/**
	 * Should apply shortcode on content.
	 *
	 * @return bool
	 */
	public static function should_apply_shortcode() {
		if (
			Post::is_woocommerce_page() ||
			( function_exists( 'is_wcfm_page' ) && is_wcfm_page() )
		) {
			return false;
		}

		return apply_filters( 'rank_math/paper/auto_generated_description/apply_shortcode', false );
	}

	/**
	 * Clears and reinitializes the object.
	 */
	public static function reset() {
		self::$instance = null;
		return self::get();
	}
}
