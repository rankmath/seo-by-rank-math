<?php
/**
 * The BuddyPress user class for the BuddyPress module.
 *
 * @since      1.0.32
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\User;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * BP_User class.
 */
class BP_User implements IPaper {

	/**
	 * Retrieves the SEO title set in the user metabox.
	 *
	 * @return string The SEO title for the user.
	 */
	public function title() {
		return '';
	}

	/**
	 * Retrieves the SEO description set in the user metabox.
	 *
	 * @return string The SEO description for the user.
	 */
	public function description() {
		$description = User::get_meta( 'description', bp_displayed_user_id() );
		if ( '' !== $description ) {
			return $description;
		}

		return Paper::get_from_options( 'author_archive_description' );
	}

	/**
	 * Retrieves the robots set in the user metabox.
	 *
	 * @return string The robots for the specified user.
	 */
	public function robots() {
		$robots = Paper::robots_combine( User::get_meta( 'robots', bp_displayed_user_id() ) );

		if ( empty( $robots ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
			$robots = Paper::robots_combine( Helper::get_settings( 'titles.author_robots' ), true );
		}

		return $robots;
	}

	/**
	 * Retrieves the advanced robots set in the user metabox.
	 *
	 * @return array The advanced robots for the specified user.
	 */
	public function advanced_robots() {
		$robots = Paper::advanced_robots_combine( User::get_meta( 'advanced_robots', bp_displayed_user_id() ) );

		if ( empty( $robots ) && Helper::get_settings( 'titles.author_custom_robots' ) ) {
			$robots = Paper::advanced_robots_combine( Helper::get_settings( 'titles.author_advanced_robots' ), true );
		}

		return $robots;
	}

	/**
	 * Retrieves the default canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		return '';
	}

	/**
	 * Retrieves the default keywords.
	 *
	 * @return string
	 */
	public function keywords() {
		return User::get_meta( 'focus_keyword', bp_displayed_user_id() );
	}
}
