<?php
/**
 * The Home Class
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\Post;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Blog class.
 */
class Blog implements IPaper {

	/**
	 * Builds the title for Homepage.
	 *
	 * @return string The title to use on homepage.
	 */
	public function title() {
		return Paper::get_from_options( 'homepage_title' );
	}

	/**
	 * Builds the description for Homepage.
	 *
	 * @return string The description to use on a homepage.
	 */
	public function description() {
		return Paper::get_from_options( 'homepage_description', [], get_bloginfo( 'description' ) );
	}

	/**
	 * Retrieves the robots for Homepage.
	 *
	 * @return string The robots specified for the homepage.
	 */
	public function robots() {
		$robots = [];

		if ( Helper::get_settings( 'titles.homepage_custom_robots' ) ) {
			$robots = Paper::robots_combine( Helper::get_settings( 'titles.homepage_robots' ) );
		}

		if ( is_paged() && Helper::get_settings( 'titles.noindex_paginated_pages' ) ) {
			$robots['index'] = 'noindex';
		}

		return $robots;
	}

	/**
	 * Retrieves the advanced robots for Homepage.
	 *
	 * @return array The advanced robots specified for the homepage.
	 */
	public function advanced_robots() {
		if ( ! Helper::get_settings( 'titles.homepage_custom_robots' ) ) {
			return [];
		}

		return Paper::advanced_robots_combine( Helper::get_settings( 'titles.homepage_advanced_robots' ) );
	}

	/**
	 * Retrieves the canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		$canonical = home_url();
		if ( Post::is_posts_page() ) {
			$posts_page_id = get_option( 'page_for_posts' );
			$canonical     = Post::get_meta( 'canonical_url', $posts_page_id );
			if ( empty( $canonical ) ) {
				$canonical = get_permalink( $posts_page_id );
			}
		}

		return [ 'canonical' => $canonical ];
	}

	/**
	 * Retrieves the keywords.
	 *
	 * @return string The focus keywords.
	 */
	public function keywords() {
		if ( ! Post::is_posts_page() ) {
			return '';
		}

		return Post::get_meta( 'focus_keyword', get_option( 'page_for_posts' ) );
	}
}
