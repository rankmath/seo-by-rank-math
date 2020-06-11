<?php
/**
 * The Author Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

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
			'@type'         => 'ProfilePage',
			'headline'      => sprintf( 'About %s', get_the_author() ),
			'datePublished' => get_the_date( 'Y-m-d' ),
			'dateModified'  => get_the_modified_date( 'Y-m-d' ),
			'about'         => $jsonld->get_author(),
		];

		return $data;
	}
}
