<?php
/**
 * The Author Class
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\User;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Author class.
 */
class Author implements IPaper {

	/**
	 * Get the SEO title set in the user metabox.
	 *
	 * @return string
	 */
	public function title() {
		$title = User::get_meta( 'title', $this->get_user_id() );
		if ( '' !== $title ) {
			return $title;
		}

		return Paper::get_from_options( 'author_archive_title' );
	}

	/**
	 * Get the SEO description set in the user metabox.
	 *
	 * @return string
	 */
	public function description() {
		$description = User::get_meta( 'description', $this->get_user_id() );
		if ( '' !== $description ) {
			return $description;
		}

		return Paper::get_from_options( 'author_archive_description' );
	}

	/**
	 * Get the robots meta value set in the user metabox.
	 *
	 * @return string
	 */
	public function robots() {
		$robots = Paper::robots_combine( User::get_meta( 'robots', $this->get_user_id() ) );

		if ( empty( $robots ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
			$robots = Paper::robots_combine( Helper::get_settings( 'titles.author_robots' ), true );
		}

		return $robots;
	}

	/**
	 * Get the advanced robots meta set in the user metabox.
	 *
	 * @return array
	 */
	public function advanced_robots() {
		$robots = Paper::advanced_robots_combine( User::get_meta( 'advanced_robots', $this->get_user_id() ) );

		if ( empty( $robots ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
			$robots = Paper::advanced_robots_combine( Helper::get_settings( 'titles.author_advanced_robots' ), true );
		}

		return $robots;
	}

	/**
	 * Get the canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		return [
			'canonical'          => get_author_posts_url( $this->get_user_id(), get_query_var( 'author_name' ) ),
			'canonical_override' => User::get_meta( 'canonical_url', $this->get_user_id() ),
		];
	}

	/**
	 * Get the meta keywords for the user (in our case, the Focus Keywords).
	 *
	 * @return string
	 */
	public function keywords() {
		return User::get_meta( 'focus_keyword', $this->get_user_id() );
	}

	/**
	 * Get the user ID on the author archive or BBPress profile.
	 *
	 * @return int
	 */
	private function get_user_id() {
		$author_id = get_query_var( 'author' );
		if ( $author_id ) {
			return $author_id;
		}

		return get_query_var( 'bbp_user_id' );
	}
}
