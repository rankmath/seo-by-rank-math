<?php
/**
 * The Restaurant Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Restaurant class.
 */
class Restaurant implements Snippet {

	/**
	 * Restaurant rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$local  = new Local;
		$entity = $local->process( $data, $jsonld );

		$entity['@type']       = 'Restaurant';
		$entity['description'] = $jsonld->parts['desc'];
		$entity['hasMenu']     = Helper::get_post_meta( 'snippet_restaurant_menu' );

		$serves_cuisine = trim( Helper::get_post_meta( 'snippet_restaurant_serves_cuisine' ) );
		if ( $serves_cuisine ) {
			$entity['servesCuisine'] = explode( ',', $serves_cuisine );
		}

		return $entity;
	}
}
