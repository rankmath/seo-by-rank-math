<?php
/**
 * The sitemap provider for post types.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Sitemap\Providers;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Sitemap\Router;
use RankMath\Sitemap\Sitemap;
use RankMath\Sitemap\Classifier;
use RankMath\Sitemap\Image_Parser;

defined( 'ABSPATH' ) || exit;

/**
 * Post type provider class.
 */
class Post_Type implements Provider {

	use Hooker;

	/**
	 * Holds the `home_url()` value to speed up loops.
	 *
	 * @var string
	 */
	protected $home_url = null;

	/**
	 * Holds image parser instance.
	 *
	 * @var Image_Parser
	 */
	protected $image_parser = null;

	/**
	 * Holds link classifier.
	 *
	 * @var Classifier
	 */
	protected $classifier = null;

	/**
	 * Static front page ID.
	 *
	 * @var int
	 */
	protected $page_on_front_id = null;

	/**
	 * Posts page ID.
	 *
	 * @var int
	 */
	protected $page_for_posts_id = null;

	/**
	 * Check if provider supports given item type.
	 *
	 * @param string $type Type string to check for.
	 *
	 * @return boolean
	 */
	public function handles_type( $type ) {
		if (
			false === post_type_exists( $type ) ||
			! Helper::get_settings( 'sitemap.pt_' . $type . '_sitemap' ) ||
			( 'attachment' === $type && Helper::get_settings( 'general.attachment_redirect_urls', true ) )
		) {
			return false;
		}

		/**
		 * Filter decision if post type is excluded from the XML sitemap.
		 *
		 * @param bool   $exclude Default false.
		 * @param string $type    Post type name.
		 */
		return ! $this->do_filter( 'sitemap/exclude_post_type', false, $type );
	}

	/**
	 * Get set of sitemaps index link data.
	 *
	 * @param int $max_entries Entries per sitemap.
	 *
	 * @return array
	 */
	public function get_index_links( $max_entries ) {
		global $wpdb;

		$post_types          = Helper::get_accessible_post_types();
		$post_types          = array_filter( $post_types, [ $this, 'handles_type' ] );
		$last_modified_times = Sitemap::get_last_modified_gmt( $post_types, true );
		$index               = [];

		foreach ( $post_types as $post_type ) {

			$total_count = $this->get_post_type_count( $post_type );
			if ( 0 === $total_count ) {
				continue;
			}

			$max_pages = 1;
			if ( $total_count > $max_entries ) {
				$max_pages = (int) ceil( $total_count / $max_entries );
			}

			$all_dates = [];
			if ( $max_pages > 1 ) {
				$sql = "
				SELECT post_modified_gmt
					FROM ( SELECT @rownum:=@rownum rownum, $wpdb->posts.post_modified_gmt
					FROM ( SELECT @rownum:=0 ) r, $wpdb->posts
						WHERE post_status IN ( 'publish', 'inherit' )
						AND post_type = %s
						ORDER BY post_modified_gmt ASC
					)
					x WHERE rownum %% %d = 0 ORDER BY post_modified_gmt DESC";

				$all_dates = $wpdb->get_col( $wpdb->prepare( $sql, $post_type, $max_entries ) ); // phpcs:ignore
			}

			for ( $page_counter = 0; $page_counter < $max_pages; $page_counter++ ) {
				$current_page = ( $max_pages > 1 ) ? ( $page_counter + 1 ) : '';
				$date         = false;

				if ( isset( $all_dates[ $page_counter ] ) ) {
					$date = $all_dates[ $page_counter ];
				} elseif ( ! empty( $last_modified_times[ $post_type ] ) ) {
					$date = $last_modified_times[ $post_type ];
				}

				$index[] = [
					'loc'     => Router::get_base_url( $post_type . '-sitemap' . $current_page . '.xml' ),
					'lastmod' => $date,
				];
			}
		}

		return $index;
	}

	/**
	 * Get set of sitemap link data.
	 *
	 * @param string $type         Sitemap type.
	 * @param int    $max_entries  Entries per sitemap.
	 * @param int    $current_page Current page of the sitemap.
	 *
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {
		$links     = [];
		$steps     = $max_entries;
		$offset    = ( $current_page > 1 ) ? ( ( $current_page - 1 ) * $max_entries ) : 0;
		$total     = ( $offset + $max_entries );
		$typecount = $this->get_post_type_count( $type );

		Sitemap::maybe_redirect( $typecount, $max_entries );
		if ( $total > $typecount ) {
			$total = $typecount;
		}

		if ( 1 === $current_page ) {
			$links = array_merge( $links, $this->get_first_links( $type ) );
		}

		if ( 0 === $typecount ) {
			return $links;
		}

		$stacked_urls = [];
		while ( $total > $offset ) {
			$posts   = $this->get_posts( $type, $steps, $offset );
			$offset += $steps;

			if ( empty( $posts ) ) {
				continue;
			}

			foreach ( $posts as $post ) {
				$post_id = (int) $post->ID;
				if ( ! Sitemap::is_object_indexable( $post_id ) ) {
					continue;
				}

				$url = $this->get_url( $post );
				if ( ! isset( $url['loc'] ) ) {
					continue;
				}

				/**
				 * Filter URL entry before it gets added to the sitemap.
				 *
				 * @param array  $url  Array of URL parts.
				 * @param string $type URL type.
				 * @param object $user Data object for the URL.
				 */
				$url = $this->do_filter( 'sitemap/entry', $url, 'post', $post );
				if ( empty( $url ) ) {
					continue;
				}

				$stacked_urls[] = $url['loc'];
				if ( $post_id === $this->get_page_for_posts_id() || $post_id === $this->get_page_on_front_id() ) {
					array_unshift( $links, $url );
					continue;
				}
				$links[] = $url;
			}

			unset( $post, $url );
		}

		return $links;
	}

	/**
	 * Get count of posts for post type.
	 *
	 * @param string $post_types Post types to retrieve count for.
	 *
	 * @return int
	 */
	protected function get_post_type_count( $post_types ) {
		global $wpdb;

		if ( ! is_array( $post_types ) ) {
			$post_types = [ $post_types ];
		}

		/**
		 * Filter JOIN query part for type count of post type.
		 *
		 * @param string $join       SQL part, defaults to empty string.
		 * @param string $post_types Post types name.
		 */
		$join_filter = $this->do_filter( 'sitemap/typecount_join', '', $post_types );

		/**
		 * Filter WHERE query part for type count of post type.
		 *
		 * @param string $where     SQL part, defaults to empty string.
		 * @param string $post_types Post types name.
		 */
		$where_filter = $this->do_filter( 'sitemap/typecount_where', '', $post_types );

		$where = $this->get_sql_where_clause( $post_types );

		$sql = "
			SELECT COUNT({$wpdb->posts}.ID)
			FROM {$wpdb->posts}
			{$join_filter}
			{$where}
			{$where_filter}";

		return (int) $wpdb->get_var( $sql ); // phpcs:ignore
	}

	/**
	 * Produces set of links to prepend at start of first sitemap page.
	 *
	 * @param string $post_type Post type to produce links for.
	 *
	 * @return array
	 */
	protected function get_first_links( $post_type ) {

		$links         = [];
		$needs_archive = true;

		if ( ! $this->get_page_on_front_id() && ( 'post' === $post_type || 'page' === $post_type ) ) {
			$needs_archive = false;
			$links[]       = [ 'loc' => $this->get_home_url() ];
		} elseif ( $this->get_page_on_front_id() && 'post' === $post_type && $this->get_page_for_posts_id() ) {
			$needs_archive = false;
			$links[]       = Sitemap::is_object_indexable( $this->get_page_for_posts_id() ) ? [ 'loc' => get_permalink( $this->get_page_for_posts_id() ) ] : '';
		}

		if ( ! $needs_archive ) {
			return array_filter( $links );
		}

		$archive_url = $this->get_post_type_archive_link( $post_type );

		/**
		 * Filter the URL Rank Math SEO uses in the XML sitemap for this post type archive.
		 *
		 * @param string $archive_url The URL of this archive
		 * @param string $post_type   The post type this archive is for.
		 */
		$archive_url = $this->do_filter( 'sitemap/post_type_archive_link', $archive_url, $post_type );

		if ( $archive_url ) {
			$links[] = [
				'loc' => $archive_url,
				'mod' => Sitemap::get_last_modified_gmt( $post_type ),
			];
		}

		return $links;
	}

	/**
	 * Get URL for a post type archive.
	 *
	 * @param string $post_type Post type.
	 *
	 * @return string|boolean URL or false if it should be excluded.
	 */
	protected function get_post_type_archive_link( $post_type ) {
		// Post archive should be excluded if it isn't front page or posts page.
		if ( 'post' === $post_type && get_option( 'show_on_front' ) !== 'posts' && ! $this->get_page_for_posts_id() ) {
			return false;
		}

		return get_post_type_archive_link( $post_type );
	}

	/**
	 * Retrieve set of posts with optimized query routine.
	 *
	 * @param array $post_types Post type to retrieve.
	 * @param int   $count      Count of posts to retrieve.
	 * @param int   $offset     Starting offset.
	 *
	 * @return object[]
	 */
	protected function get_posts( $post_types, $count, $offset ) {
		global $wpdb;

		if ( ! is_array( $post_types ) ) {
			$post_types = [ $post_types ];
		}

		$where = $this->get_sql_where_clause( $post_types );

		// Also see http://explainextended.com/2009/10/23/mysql-order-by-limit-performance-late-row-lookups/.
		$sql = "
			SELECT l.ID, post_title, post_content, post_name, post_parent, post_author, post_modified_gmt, post_date, post_date_gmt, post_type
			FROM (
				SELECT {$wpdb->posts}.ID
				FROM {$wpdb->posts}
				{$where}
				ORDER BY {$wpdb->posts}.post_modified DESC LIMIT %d OFFSET %d
			)
			o JOIN {$wpdb->posts} l ON l.ID = o.ID
		";

		$posts = $wpdb->get_results( $wpdb->prepare( $sql, $count, $offset ) ); // phpcs:ignore

		$post_ids = [];
		foreach ( $posts as $post ) {
			$post->post_status = 'publish';
			$post->filter      = 'sample';
			$post_ids[]        = $post->ID;
		}

		update_meta_cache( 'post', $post_ids );

		return $posts;
	}

	/**
	 * Get where clause to query data.
	 *
	 * @param array $post_types Post types slug.
	 *
	 * @return string
	 */
	protected function get_sql_where_clause( $post_types ) {
		global $wpdb;

		$join   = '';
		$status = "{$wpdb->posts}.post_status = 'publish'";

		// Based on WP_Query->get_posts(). R.
		if ( in_array( 'attachment', $post_types, true ) ) {
			$join   = " LEFT JOIN {$wpdb->posts} AS p2 ON ({$wpdb->posts}.post_parent = p2.ID) ";
			$status = "p2.post_status = 'publish'";
		}

		$where_clause = "
		{$join}
		WHERE {$status}
			AND {$wpdb->posts}.post_type IN ( '" . join( "', '", esc_sql( $post_types ) ) . "' )
			AND {$wpdb->posts}.post_password = ''
		";

		return $where_clause;
	}

	/**
	 * Produce array of URL parts for given post object.
	 *
	 * @param object $post Post object to get URL parts for.
	 *
	 * @return array|boolean
	 */
	protected function get_url( $post ) {
		$url = [];

		/**
		 * Filter the URL Rank Math SEO uses in the XML sitemap.
		 *
		 * Note that only absolute local URLs are allowed as the check after this removes external URLs.
		 *
		 * @param string $url  URL to use in the XML sitemap
		 * @param object $post Post object for the URL.
		 */
		$url['loc'] = $this->do_filter( 'sitemap/xml_post_url', get_permalink( $post ), $post );

		/**
		 * Do not include external URLs.
		 *
		 * @see https://wordpress.org/plugins/page-links-to/ can rewrite permalinks to external URLs.
		 */
		if ( 'external' === $this->get_classifier()->classify( $url['loc'] ) ) {
			return false;
		}

		$modified = max( $post->post_modified_gmt, $post->post_date_gmt );
		if ( '0000-00-00 00:00:00' !== $modified ) {
			$url['mod'] = $modified;
		}

		$canonical = Helper::get_post_meta( 'canonical_url', $post->ID );
		if ( '' !== $canonical && $canonical !== $url['loc'] ) {
			/*
			 * Let's assume that if a canonical is set for this page and it's different from
			 * the URL of this post, that page is either already in the XML sitemap OR is on
			 * an external site, either way, we shouldn't include it here.
			 */
			return false;
		}

		$url['images'] = ! is_null( $this->get_image_parser() ) ? $this->get_image_parser()->get_images( $post ) : [];

		return $url;
	}

	/**
	 * Get front page ID.
	 *
	 * @return int
	 */
	protected function get_page_on_front_id() {
		if ( is_null( $this->page_on_front_id ) ) {
			$this->page_on_front_id = intval( get_option( 'page_on_front' ) );
		}

		return $this->page_on_front_id;
	}

	/**
	 * Get page for posts ID.
	 *
	 * @return int
	 */
	protected function get_page_for_posts_id() {
		if ( is_null( $this->page_for_posts_id ) ) {
			$this->page_for_posts_id = intval( get_option( 'page_for_posts' ) );
		}

		return $this->page_for_posts_id;
	}

	/**
	 * Get the Image Parser.
	 *
	 * @return Image_Parser
	 */
	protected function get_image_parser() {
		if ( is_null( $this->image_parser ) ) {
			$this->image_parser = new Image_Parser();
		}

		return $this->image_parser;
	}

	/**
	 * Get the link classifier.
	 *
	 * @return Classifier
	 */
	protected function get_classifier() {
		if ( is_null( $this->classifier ) ) {
			$this->classifier = new Classifier( $this->get_home_url() );
		}

		return $this->classifier;
	}

	/**
	 * Get Home URL.
	 *
	 * This has been moved from the constructor because wp_rewrite is not available on plugins_loaded in multisite.
	 * It will now be requested on need and not on initialization.
	 *
	 * @return string
	 */
	protected function get_home_url() {
		if ( is_null( $this->home_url ) ) {
			$this->home_url = user_trailingslashit( get_home_url() );
		}

		return $this->home_url;
	}
}
