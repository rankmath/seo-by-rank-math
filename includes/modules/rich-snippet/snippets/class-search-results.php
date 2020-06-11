<?php
/**
 * The Search Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

defined( 'ABSPATH' ) || exit;

/**
 * Search_Results class.
 */
class Search_Results implements Snippet {

	/**
	 * Sets the Schema structured data for the SearchResultsPage.
	 *
	 * @link https://schema.org/SearchResultsPage
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$data['SearchResultsPage'] = [
			'@type' => 'SearchResultsPage',
		];

		return $data;
	}
}
