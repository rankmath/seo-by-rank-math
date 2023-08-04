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
	 * User object.
	 *
	 * @var User
	 */
	private $object;

	/**
	 * Retrieve User instance.
	 *
	 * @param  User|object|int $user_id User to get using (int) user_id.
	 * @return User|false User object, false otherwise.
	 */
	public static function get( $user_id = 0 ) {
		$user = User::get( $user_id );
		return $user;
	}

	/**
	 * Set user object.
	 *
	 * @param User $object Current user object.
	 */
	public function set_object( $object ) {
		$this->object = $object;
	}

	/**
	 * Get the SEO title set in the user metabox.
	 *
	 * @return string
	 */
	public function title() {
		$user_id = $this->get_user_id();
		$title   = User::get_meta( 'title', $user_id );
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
		$user_id     = $this->get_user_id();
		$description = User::get_meta( 'description', $user_id );
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
		$user_id = $this->get_user_id();
		$robots  = Paper::robots_combine( User::get_meta( 'robots', $user_id ) );

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
		$user_id = $this->get_user_id();
		$robots  = Paper::advanced_robots_combine( User::get_meta( 'advanced_robots', $user_id ) );

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
		$user_id = $this->get_user_id();
		return [
			'canonical'          => get_author_posts_url( $user_id, get_query_var( 'author_name' ) ),
			'canonical_override' => User::get_meta( 'canonical_url', $user_id ),
		];
	}

	/**
	 * Get the meta keywords for the user (in our case, the Focus Keywords).
	 *
	 * @return string
	 */
	public function keywords() {
		$user_id = $this->get_user_id();
		return User::get_meta( 'focus_keyword', $user_id );
	}

	/**
	 * Get the user ID on the author archive or BBPress profile.
	 *
	 * @return int
	 */
	private function get_user_id() {
		if ( ! empty( $this->object ) ) {
			return $this->object->ID;
		}
		$author_id = get_query_var( 'author' );
		if ( $author_id ) {
			return $author_id;
		}

		return get_query_var( 'bbp_user_id' );
	}
}
