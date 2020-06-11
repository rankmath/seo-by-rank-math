<?php
/**
 * The Schema Module
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * RichSnippet class.
 */
class RichSnippet {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {

		if ( is_admin() ) {
			new Admin;
		}
		$this->action( 'wp', 'integrations' );

		new Blocks;
		new Snippet_Shortcode;
	}

	/**
	 * Initialize integrations.
	 */
	public function integrations() {
		$type = get_query_var( 'sitemap' );
		if ( ! empty( $type ) ) {
			return;
		}

		new JsonLD;
	}
}
