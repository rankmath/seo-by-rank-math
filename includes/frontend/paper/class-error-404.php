<?php
/**
 * The 404 paper.
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * 404 Error.
 */
class Error_404 implements IPaper {

	/**
	 * Retrieves the SEO title.
	 *
	 * @return string
	 */
	public function title() {
		return Paper::get_from_options( '404_title', [], esc_html__( 'Page not found', 'rank-math' ) );
	}

	/**
	 * Retrieves the SEO description.
	 *
	 * @return string
	 */
	public function description() {
		return '';
	}

	/**
	 * Retrieves the robots.
	 *
	 * @return string
	 */
	public function robots() {
		return [ 'index' => 'noindex' ];
	}

	/**
	 * Retrieves the advanced robots.
	 *
	 * @return array
	 */
	public function advanced_robots() {
		return [];
	}

	/**
	 * Retrieves the canonical URL.
	 *
	 * @return array
	 */
	public function canonical() {
		return [];
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
