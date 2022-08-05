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
	 * Get the SEO title for a date archive.
	 *
	 * @return string
	 */
	public function title() {
		return Paper::get_from_options( 'date_archive_title' );
	}

	/**
	 * Get the SEO description for a date archive.
	 *
	 * @return string
	 */
	public function description() {
		return Paper::get_from_options( 'date_archive_description' );
	}

	/**
	 * Get the robots meta for a date archive.
	 *
	 * @return string
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
	 * Get the advanced robots meta for a date archive.
	 *
	 * @return array
	 */
	public function advanced_robots() {
		return Paper::advanced_robots_combine( Helper::get_settings( 'titles.date_advanced_robots' ) );
	}

	/**
	 * Get the canonical URL for the current page.
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
	 * Get the meta keywords.
	 *
	 * @return string
	 */
	public function keywords() {
		return '';
	}
}
