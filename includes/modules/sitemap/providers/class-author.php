<?php
/**
 * The sitemap provider for author archives.
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

use DateTime;
use DateTimeZone;
use RankMath\Helper;
use RankMath\Sitemap\Router;
use RankMath\Sitemap\Sitemap;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Author class.
 */
class Author implements Provider {

	use Hooker;

	/**
	 * The constructor.
	 */
	public function __construct() {
		$this->filter( 'rank_math/sitemap/author/query', 'exclude_users', 5 );
		$this->filter( 'rank_math/sitemap/author/query', 'exclude_roles', 5 );
		$this->filter( 'rank_math/sitemap/author/query', 'exclude_post_types', 5 );
	}

	/**
	 * Check if provider supports given item type.
	 *
	 * @param  string $type Type string to check for.
	 * @return boolean
	 */
	public function handles_type( $type ) {
		return 'author' === $type && Helper::get_settings( 'sitemap.authors_sitemap' );
	}

	/**
	 * Get set of sitemaps index link data.
	 *
	 * @param  int $max_entries Entries per sitemap.
	 * @return array
	 */
	public function get_index_links( $max_entries ) {
		if ( ! Helper::get_settings( 'sitemap.authors_sitemap' ) ) {
			return [];
		}

		$users = $this->get_users();

		if ( empty( $users ) ) {
			return [];
		}

		$page       = 1;
		$index      = [];
		$user_pages = array_chunk( $users, $max_entries );

		if ( 1 === count( $user_pages ) ) {
			$page = '';
		}

		foreach ( $user_pages as $user_page ) {
			$user = array_shift( $user_page ); // Time descending, first user on page is most recently updated.
			$item = $this->do_filter(
				'sitemap/index/entry',
				[
					'loc'     => Router::get_base_url( 'author-sitemap' . $page . '.xml' ),
					'lastmod' => '@' . $user->last_update,
				],
				'author',
				$user
			);

			if ( ! $item ) {
				continue;
			}

			$index[] = $item;

			$page++;
		}

		return $index;
	}

	/**
	 * Get set of sitemap link data.
	 *
	 * @param  string $type         Sitemap type.
	 * @param  int    $max_entries  Entries per sitemap.
	 * @param  int    $current_page Current page of the sitemap.
	 * @return array
	 */
	public function get_sitemap_links( $type, $max_entries, $current_page ) {
		$links = [];

		if ( $current_page < 1 ) {
			$current_page = 1;
		}

		$users = $this->get_users(
			[
				'offset' => ( $current_page - 1 ) * $max_entries,
				'number' => $max_entries,
			]
		);

		if ( empty( $users ) ) {
			return $links;
		}

		Sitemap::maybe_redirect( count( $users ), $max_entries );
		foreach ( $users as $user ) {
			$url = $this->get_sitemap_url( $user );
			if ( ! empty( $url ) ) {
				$links[] = $url;
			}
		}

		return $links;
	}

	/**
	 * Get sitemap urlset.
	 *
	 * @param WP_User $user User instance.
	 *
	 * @return bool|array
	 */
	private function get_sitemap_url( $user ) {
		$author_link = get_author_posts_url( $user->ID );
		if ( empty( $author_link ) ) {
			return false;
		}

		$mod = isset( $user->last_update ) ? date( 'Y-m-d H:i:s', $user->last_update ) : strtotime( $user->user_registered );

		$date = new DateTime( $mod, new DateTimeZone( 'UTC' ) );

		$url = [
			'loc' => $author_link,
			'mod' => $date->format( DATE_W3C ),
		];

		/** This filter is documented at includes/modules/sitemap/providers/class-post-type.php */
		return $this->do_filter( 'sitemap/entry', $url, 'user', $user );
	}

	/**
	 * Retrieve users, taking account of all necessary exclusions.
	 *
	 * @param  array $args Arguments to add.
	 * @return array
	 */
	public function get_users( $args = [] ) {
		$defaults = [
			'orderby'    => 'meta_value_num',
			'order'      => 'DESC',
			'meta_query' => [
				'relation' => 'AND',
				[
					'relation' => 'OR',
					[
						'key' => 'last_update',
					],
					[
						'key'     => 'last_update',
						'compare' => 'NOT EXISTS',
					],
				],
				[
					'relation' => 'OR',
					[
						'key'     => 'rank_math_robots',
						'value'   => 'noindex',
						'compare' => 'NOT LIKE',
					],
					[
						'key'     => 'rank_math_robots',
						'compare' => 'NOT EXISTS',
					],
				],
			],
		];

		$args = $this->do_filter( 'sitemap/author/query', wp_parse_args( $args, $defaults ) );

		return get_users( $args );
	}

	/**
	 * Exclude users.
	 *
	 * @param array $args Array of user query arguments.
	 *
	 * @return array
	 */
	public function exclude_users( $args ) {
		$exclude = Helper::get_settings( 'sitemap.exclude_users' );
		if ( ! empty( $exclude ) ) {
			$args['exclude'] = wp_parse_id_list( $exclude );
		}

		return $args;
	}

	/**
	 * Exclude roles.
	 *
	 * @param array $args Array of user query arguments.
	 *
	 * @return array
	 */
	public function exclude_roles( $args ) {
		$exclude_roles = Helper::get_settings( 'sitemap.exclude_roles' );
		if ( ! empty( $exclude_roles ) ) {
			$args['role__not_in'] = $exclude_roles;
		}

		return $args;
	}

	/**
	 * Exclude post types.
	 *
	 * @param array $args Array of user query arguments.
	 *
	 * @return array
	 */
	public function exclude_post_types( $args ) {
		// Exclude post types.
		$public_post_types = get_post_types( [ 'public' => true ] );

		// We're not supporting sitemaps for author pages for attachments.
		unset( $public_post_types['attachment'] );

		$args['has_published_posts'] = array_keys( $public_post_types );

		return $args;
	}
}
