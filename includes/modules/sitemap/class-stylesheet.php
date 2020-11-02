<?php
/**
 * The Sitemap Stylesheet
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Sitemap
 * @author     Rank Math <support@rankmath.com>
 *
 * @copyright Copyright (C) 2008-2019, Yoast BV
 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
 */

namespace RankMath\Sitemap;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Stylesheet class.
 */
class Stylesheet extends XML {

	use Hooker;

	/**
	 * Spits out the XSL for the XML sitemap.
	 *
	 * @param string $type Sitemap type.
	 */
	public function output( $type ) {
		$this->type = $type;
		$this->send_headers();

		/* translators: 1. separator, 2. blogname */
		$title = sprintf( __( 'XML Sitemap %1$s %2$s', 'rank-math' ), '-', get_bloginfo( 'name', 'display' ) );

		if ( 'main' !== $type ) {
			/**
			 * Fires for the output of XSL for XML sitemaps, other than type "main".
			 */
			$this->do_action( "sitemap/xsl_{$type}", $title );
			die;
		}

		require_once 'sitemap-xsl.php';
		die;
	}
}
