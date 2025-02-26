<?php
/**
 * The sitemap index runner class.
 *
 * @since      1.0.42
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Sitemap;

use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Sitemap Index class.
 */
class Sitemap_Index implements Runner {

	use Hooker;

	/**
	 * The hooks.
	 */
	public function hooks() {
		$this->filter( 'robots_txt', 'add_sitemap_directive', 0, 2 );
		$this->filter( 'redirect_canonical', 'redirect_canonical' );
	}

	/**
	 * Adds the sitemap index to robots.txt.
	 *
	 * @param string $output    robots.txt output.
	 * @param bool   $is_public Whether the site is public or not.
	 *
	 * @return string robots.txt output.
	 */
	public function add_sitemap_directive( $output, $is_public ) {
		if (
			'0' === $is_public ||
			Str::contains( 'Sitemap:', $output ) ||
			Str::contains( 'sitemap:', $output )
		) {
			return $output;
		}

		$sitemap_url = esc_url( Router::get_base_url( Sitemap::get_sitemap_index_slug() . '.xml' ) );
		return $output . "\nSitemap: {$sitemap_url}\n";
	}

	/**
	 * Stop trailing slashes on `sitemap.xml` URLs.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 *
	 * @param string $redirect The redirect URL currently determined.
	 *
	 * @return boolean|string $redirect
	 */
	public function redirect_canonical( $redirect ) {
		if ( get_query_var( 'sitemap' ) || get_query_var( 'xsl' ) ) {
			return false;
		}

		return $redirect;
	}
}
