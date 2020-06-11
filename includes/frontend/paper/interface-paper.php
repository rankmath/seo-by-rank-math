<?php
/**
 * The Paper Interface
 *
 * @since      1.0.22
 * @package    RankMath
 * @subpackage RankMath\Paper
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Paper interface.
 */
interface IPaper {

	/**
	 * Retrieves the SEO title.
	 *
	 * @return string
	 */
	public function title();

	/**
	 * Retrieves the SEO description.
	 *
	 * @return string
	 */
	public function description();

	/**
	 * Retrieves the robots.
	 *
	 * @return string
	 */
	public function robots();

	/**
	 * Retrieves the advanced robots.
	 *
	 * @return array
	 */
	public function advanced_robots();

	/**
	 * Retrieves the canonical URL.
	 *
	 * @return array
	 */
	public function canonical();

	/**
	 * Retrieves the keywords.
	 *
	 * @return string The focus keywords.
	 */
	public function keywords();
}
