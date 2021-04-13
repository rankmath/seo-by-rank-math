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

use RankMath\Helper;

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
		$author_id           = is_singular() ? $jsonld->post->post_author : get_the_author_meta( 'ID' );
		$data['ProfilePage'] = [
			'@type' => 'Person',
			'@id'   => get_author_posts_url( $author_id ),
			'name'  => get_the_author(),
		];
		$this->add_image( $data['ProfilePage'], $author_id, $jsonld );
		$this->add_same_as( $data['ProfilePage'], $author_id );
		$this->add_works_for( $data['ProfilePage'], $data );

		if ( is_author() && ! empty( $data['WebPage'] ) ) {
			$data['ProfilePage']['mainEntityOfPage'] = [
				'@id' => $data['WebPage']['@id'],
			];
		}

		return $data;
	}

	/**
	 * Sets the Schema structured data for the ProfilePage.
	 *
	 * @param array $entity    Author schema data.
	 * @param int   $author_id Author ID.
	 */
	private function add_same_as( &$entity, $author_id ) {
		$same_as = [
			get_the_author_meta( 'user_url', $author_id ),
			get_user_meta( $author_id, 'facebook', true ),
		];

		if ( $twitter = get_user_meta( $author_id, 'twitter', true ) ) { // phpcs:ignore
			$same_as[] = 'https://twitter.com/' . $twitter;
		}

		$same_as = array_filter( $same_as );
		if ( empty( $same_as ) ) {
			return;
		}

		$entity['sameAs'] = array_values( $same_as );
	}

	/**
	 * Add image to Person entity.
	 *
	 * @param array  $entity    Author schema data.
	 * @param int    $author_id Author ID.
	 * @param JsonLD $jsonld    JsonLD Instance.
	 */
	private function add_image( &$entity, $author_id, $jsonld ) {
		$entity['image'] = [
			'@type'   => 'ImageObject',
			'url'     => get_avatar_url( $author_id ),
			'caption' => get_the_author(),
		];

		$jsonld->add_prop( 'language', $entity['image'] );
	}

	/**
	 * Add worksFor property.
	 *
	 * @param array $entity Author schema data.
	 * @param array $data   Schema Data.
	 */
	private function add_works_for( &$entity, $data ) {
		if (
			empty( $data['publisher'] ) ||
			in_array( 'Person', (array) $data['publisher']['@type'], true )
		) {
			return;
		}

		$entity['worksFor'] = [ '@id' => $data['publisher']['@id'] ];
	}
}
