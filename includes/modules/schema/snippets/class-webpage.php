<?php
/**
 * The Webpage Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Paper\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Webpage class.
 */
class Webpage implements Snippet {

	/**
	 * Outputs code to allow recognition of the internal search engine.
	 *
	 * @link https://developers.google.com/structured-data/site-name
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type' => $this->get_type(),
			'@id'   => Paper::get()->get_canonical() . '#webpage',
			'url'   => Paper::get()->get_canonical(),
			'name'  => Paper::get()->get_title(),
		];

		if ( is_singular() ) {
			$entity['datePublished'] = $jsonld->parts['published'];
			$entity['dateModified']  = $jsonld->parts['modified'];
		}

		if ( is_home() ) {
			$jsonld->add_prop( 'publisher', $entity, 'about', $data );
		}

		$jsonld->add_prop( 'is_part_of', $entity, 'website' );
		$jsonld->add_prop( 'thumbnail', $entity, 'primaryImageOfPage', $data );
		$jsonld->add_prop( 'language', $entity );

		if ( isset( $data['BreadcrumbList'] ) ) {
			$entity['breadcrumb'] = [ '@id' => $data['BreadcrumbList']['@id'] ];
		}

		$data['WebPage'] = $entity;

		return $data;
	}

	/**
	 * Get WebPage type.
	 *
	 * @return string
	 */
	private function get_type() {
		$hash = [
			'SearchResultsPage' => is_search(),
			'ProfilePage'       => is_author(),
			'CollectionPage'    => is_home() || is_archive(),
		];

		return ! empty( array_filter( $hash ) ) ? key( array_filter( $hash ) ) : 'WebPage';
	}
}
