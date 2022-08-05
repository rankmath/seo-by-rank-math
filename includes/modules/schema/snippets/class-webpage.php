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

use RankMath\Helper;
use RankMath\Paper\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Webpage class.
 */
class Webpage implements Snippet {

	/**
	 * Generate WebPage JSON-LD.
	 *
	 * @link https://schema.org/WebPage
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

		if ( is_front_page() ) {
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
	 * Get WebPage type depending on the current page.
	 *
	 * @return string
	 */
	private function get_type() {
		$about_page   = Helper::get_settings( 'titles.local_seo_about_page' );
		$contact_page = Helper::get_settings( 'titles.local_seo_contact_page' );
		$hash         = [
			'SearchResultsPage' => is_search(),
			'ProfilePage'       => is_author(),
			'CollectionPage'    => is_home() || is_archive(),
			'AboutPage'         => $about_page && is_page( $about_page ),
			'ContactPage'       => $contact_page && is_page( $contact_page ),
		];

		return ! empty( array_filter( $hash ) ) ? key( array_filter( $hash ) ) : 'WebPage';
	}
}
