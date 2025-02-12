<?php
/**
 * The Website Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Website class.
 */
class Website implements Snippet {

	/**
	 * Generate WebSite JSON-LD.
	 *
	 * @link https://schema.org/WebSite
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$data['WebSite'] = [
			'@type' => 'WebSite',
			'@id'   => home_url( '/#website' ),
			'url'   => get_home_url(),
			'name'  => $jsonld->get_website_name(),
		];

		$alternate_name = Helper::get_settings( 'titles.website_alternate_name' );
		if ( $alternate_name ) {
			$data['WebSite']['alternateName'] = $alternate_name;
		}
		$jsonld->add_prop( 'publisher', $data['WebSite'], 'publisher', $data );
		$jsonld->add_prop( 'language', $data['WebSite'] );

		/**
		 * Disable the JSON-LD output for the Sitelinks Searchbox.
		 *
		 * @param bool $disable Display or not the JSON-LD for the Sitelinks Searchbox.
		 */
		if ( apply_filters( 'rank_math/json_ld/disable_search', ! is_front_page() || is_paged() ) ) {
			return $data;
		}

		/**
		 * Change the search URL in the JSON-LD.
		 *
		 * @param string $search_url The search URL with `{search_term_string}` placeholder.
		 */
		$search_url = apply_filters( 'rank_math/json_ld/search_url', home_url( '/?s={search_term_string}' ) );

		$data['WebSite']['potentialAction'] = [
			'@type'       => 'SearchAction',
			'target'      => $search_url,
			'query-input' => 'required name=search_term_string',
		];

		return $data;
	}
}
