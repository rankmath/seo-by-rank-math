<?php
/**
 * The Sitemap module - HTML Sitemap feature.
 *
 * @since      1.0.104
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap\Html;

use RankMath\Helper;
use RankMath\Sitemap\Providers\Taxonomy;
use RankMath\Traits\Hooker;
use RankMath\Sitemap\Cache;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap class.
 */
class Sitemap extends Taxonomy {

	use Hooker;

	/**
	 * Constructor.
	 */
	public function __construct() {
		if ( ! Helper::get_settings( 'sitemap.html_sitemap' ) ) {
			return;
		}

		$this->cache      = new Cache();
		$this->generators = [
			'posts'   => new Posts(),
			'terms'   => new Terms(),
			'authors' => new Authors(),
		];

		$display_mode = Helper::get_settings( 'sitemap.html_sitemap_display' );
		if ( 'page' === $display_mode ) {
			$this->action( 'the_content', 'show_on_page' );
		} elseif ( 'shortcode' === $display_mode ) {
			add_shortcode( 'rank_math_html_sitemap', [ $this, 'shortcode' ] );

			// Compatibility code for the [aioseo_html_sitemap] shortcode.
			add_shortcode( 'aioseo_html_sitemap', [ $this, 'shortcode' ] );
		}
	}

	/**
	 * Get the HTML sitemap cache.
	 *
	 * @param string $name     Name of the cache.
	 *
	 * @return string
	 */
	private function get_cache( $name ) {
		if ( true !== \RankMath\Sitemap\Sitemap::is_cache_enabled() ) {
			return false;
		}
		return $this->cache->get_sitemap( $name, 1, true );
	}

	/**
	 * Set the HTML sitemap cache.
	 *
	 * @param string $name     Name of the cache.
	 * @param string $content  Content of the cache.
	 */
	private function set_cache( $name, $content ) {
		$this->cache->store_sitemap( $name, 1, $content, true );
	}

	/**
	 * Get generator.
	 *
	 * @param string $generator Generator name.
	 *
	 * @return object
	 */
	private function get_generator( $generator ) {
		if ( ! isset( $this->generators[ $generator ] ) ) {
			return false;
		}

		return $this->generators[ $generator ];
	}

	/**
	 * Get the HTML sitemap output.
	 *
	 * @return string
	 */
	public function get_output() {
		$post_types = self::get_post_types();
		$taxonomies = Helper::get_accessible_taxonomies();
		$taxonomies = array_filter( $taxonomies, [ $this, 'handles_type' ] );

		/**
		 * Filter the setting of excluding empty terms from the XML sitemap.
		 *
		 * @param boolean $exclude        Defaults to true.
		 * @param array   $taxonomies     Array of names for the taxonomies being processed.
		 */
		$hide_empty = $this->do_filter( 'sitemap/exclude_empty_terms', true, $taxonomies );

		$show_dates = Helper::get_settings( 'sitemap.html_sitemap_show_dates' );
		$output     = [];

		$output[] = '<div class="rank-math-html-sitemap">';
		foreach ( $post_types as $post_type ) {
			$cached = $this->get_cache( $post_type );
			if ( ! empty( $cached ) ) {
				$output[] = $cached;
				continue;
			}

			$sitemap = $this->get_generator( 'posts' )->generate_sitemap( $post_type, $show_dates );
			$this->set_cache( $post_type, $sitemap );
			$output[] = $sitemap;
		}

		if ( ! empty( $taxonomies ) ) {
			foreach ( $taxonomies as $taxonomy => $object ) {

				$cached = $this->get_cache( $taxonomy );
				if ( ! empty( $cached ) ) {
					$output[] = $cached;
					continue;
				}

				$sitemap = $this->get_generator( 'terms' )->generate_sitemap(
					$taxonomy,
					$show_dates,
					[ 'hide_empty' => $hide_empty ]
				);
				$this->set_cache( $taxonomy, $sitemap );
				$output[] = $sitemap;
			}
		}

		if ( $this->should_show_author_sitemap() ) {
			$cached = $this->get_cache( 'author' );
			if ( ! empty( $cached ) ) {
				$output[] = $cached;
			} else {
				$sitemap = $this->get_generator( 'authors' )->generate_sitemap();
				$this->set_cache( 'author', $sitemap );
				$output[] = $sitemap;
			}
		}

		$output[] = '</div>';

		return implode( '', $output );
	}

	/**
	 * Get post types to be included in the HTML sitemap.
	 *
	 * @return array
	 */
	public static function get_post_types() {
		$post_types = [];
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( ! Helper::get_settings( "sitemap.pt_{$post_type}_html_sitemap" ) ) {
				continue;
			}

			$post_types[] = $post_type;
		}

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap_post_types' - Allow changing the post types to be included in the HTML sitemap.
		 *
		 * @var array $post_types The post types to be included in the HTML sitemap.
		 */
		return apply_filters( 'rank_math/sitemap/html_sitemap_post_types', $post_types );
	}

	/**
	 * Check if author sitemap should be shown or not.
	 */
	private function should_show_author_sitemap() {
		$show = Helper::get_settings( 'sitemap.authors_html_sitemap' );
		if ( ! $show ) {
			return false;
		}

		$disable_author_archives = Helper::get_settings( 'titles.disable_author_archives' );
		if ( $disable_author_archives ) {
			return false;
		}

		$robots = Helper::get_settings( 'titles.author_robots' );
		if ( is_array( $robots ) && in_array( 'noindex', $robots, true ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get taxonomies to be included in the HTML sitemap.
	 *
	 * @return array
	 */
	public static function get_taxonomies() {
		$taxonomies = [];
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy ) {
			if ( ! Helper::get_settings( "sitemap.tax_{$taxonomy->name}_html_sitemap" ) ) {
				continue;
			}

			$taxonomies[] = $taxonomy->name;
		}

		/**
		 * Filter: 'rank_math/sitemap/html_sitemap_taxonomies' - Allow changing the taxonomies to be included in the HTML sitemap.
		 *
		 * @var array $taxonomies The taxonomies to be included in the HTML sitemap.
		 */
		return apply_filters( 'rank_math/sitemap/html_sitemap_taxonomies', $taxonomies );
	}

	/**
	 * Show sitemap on a page (after content).
	 *
	 * @param mixed $content The page content.
	 */
	public function show_on_page( $content ) {
		if ( ! is_page() ) {
			return $content;
		}

		if ( ! is_main_query() ) {
			return $content;
		}

		$post_id = get_the_ID();
		if ( (int) Helper::get_settings( 'sitemap.html_sitemap_page' ) !== $post_id ) {
			return $content;
		}

		return $content . $this->get_output();
	}

	/**
	 * Shortcode callback.
	 */
	public function shortcode() {
		return $this->get_output();
	}
}
