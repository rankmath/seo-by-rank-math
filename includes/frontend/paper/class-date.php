<?php
/**
 * The Date Class
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
 * Date class.
 */
class Date implements IPaper {

	/**
	 * Builds the title for a date archive
	 *
	 * @return string The title to use on a date archive.
	 */
	public function title() {
		return Paper::get_from_options( 'date_archive_title' );
	}

	/**
	 * Builds the description for a date archive
	 *
	 * @return string The description to use on a date archive.
	 */
	public function description() {
		return Paper::get_from_options( 'date_archive_description' );
	}

	/**
	 * Retrieves the robots for a date archive.
	 *
	 * @return string The robots to use on a date archive.
	 */
	public function robots() {
		$robots = [];
		$robots = Paper::robots_combine( Helper::get_settings( 'titles.date_archive_robots' ) );
		if ( Helper::get_settings( 'titles.disable_date_archives' ) ) {
			$robots['index'] = 'noindex';
		}

		return $robots;
	}

	/**
	 * Retrieves the advanced robots for a date archive.
	 *
	 * @return array The advanced robots to use on a date archive.
	 */
	public function advanced_robots() {
		return Paper::advanced_robots_combine( Helper::get_settings( 'titles.date_advanced_robots' ) );
	}

	/**
	 * This function normally outputs the canonical but is also used in other places to retrieve
	 * the canonical URL for the current page.
	 *
	 * @return array
	 */
	public function canonical() {
		$canonical = '';
		if ( is_day() ) {
			$canonical = get_day_link( get_query_var( 'year' ), get_query_var( 'monthnum' ), get_query_var( 'day' ) );
		} elseif ( is_month() ) {
			$canonical = get_month_link( get_query_var( 'year' ), get_query_var( 'monthnum' ) );
		} elseif ( is_year() ) {
			$canonical = get_year_link( get_query_var( 'year' ) );
		}

		return [ 'canonical' => $canonical ];
	}

	/**
	 * Retrieves meta keywords.
	 *
	 * @return string
	 */
	public function keywords() {
		return '';
	}
}
