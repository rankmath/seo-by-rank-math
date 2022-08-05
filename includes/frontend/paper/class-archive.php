<?php
/**
 * The PostArchive Class
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Archive class.
 */
class Archive implements IPaper {

	/**
	 * Build the title for a post type archive.
	 *
	 * @return string
	 */
	public function title() {
		$post_type = $this->get_queried_post_type();

		return Paper::get_from_options( "pt_{$post_type}_archive_title", [], '%pt_plural% Archive %page% %sep% %sitename%' );
	}

	/**
	 * Build the description for a post type archive.
	 *
	 * @return string
	 */
	public function description() {
		$post_type = $this->get_queried_post_type();

		return Paper::get_from_options( "pt_{$post_type}_archive_description", [], '%pt_plural% Archive %page% %sep% %sitename%' );
	}

	/**
	 * Retrieves the robots for a post type archive.
	 *
	 * @return string The robots to use on a post type archive.
	 */
	public function robots() {
		$robots    = [];
		$post_type = $this->get_queried_post_type();

		if ( Helper::get_settings( "titles.pt_{$post_type}_custom_robots" ) ) {
			$robots = Paper::robots_combine( Helper::get_settings( "titles.pt_{$post_type}_robots" ) );
		}

		return $robots;
	}

	/**
	 * Retrieves the advanced robots for a post type archive.
	 *
	 * @return array The advanced robots to use on a post type archive.
	 */
	public function advanced_robots() {
		$robots    = [];
		$post_type = $this->get_queried_post_type();

		if ( Helper::get_settings( "titles.pt_{$post_type}_custom_robots" ) ) {
			$robots = Paper::advanced_robots_combine( Helper::get_settings( "titles.pt_{$post_type}_advanced_robots" ) );
		}

		return $robots;
	}

	/**
	 * Get the canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		return [ 'canonical' => get_post_type_archive_link( $this->get_queried_post_type() ) ];
	}

	/**
	 * Get the meta keywords.
	 *
	 * @return string
	 */
	public function keywords() {
		return '';
	}

	/**
	 * Get the queried post type.
	 *
	 * @return string
	 */
	private function get_queried_post_type() {
		$post_type = get_query_var( 'post_type' );
		if ( is_array( $post_type ) ) {
			$post_type = reset( $post_type );
		}

		return $post_type;
	}
}
