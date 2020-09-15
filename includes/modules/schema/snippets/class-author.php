<?php
/**
 * The Author Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

defined( 'ABSPATH' ) || exit;

/**
 * Author class.
 */
class Author implements Snippet {

	/**
	 * Sets the Schema structured data for the ProfilePage.
	 *
	 * @link https://schema.org/ProfilePage
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$data['ProfilePage'] = [
			'@type' => 'Person',
			'name'  => get_the_author(),
		];

		if ( ! empty( $data['WebPage'] ) ) {
			$data['ProfilePage']['mainEntityOfPage'] = [
				'@id' => $data['WebPage']['@id'],
			];
		}

		return $data;
	}
}
