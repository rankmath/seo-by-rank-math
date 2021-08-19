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
use RankMath\User;
use RankMath\Paper\Paper;

defined( 'ABSPATH' ) || exit;

/**
 * Author class.
 */
class Author implements Snippet {

	/**
	 * Add Author entity in JSON-LD data.
	 *
	 * @link https://schema.org/Person
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$is_archive_disabled = Helper::get_settings( 'titles.disable_author_archives' );
		$author_id           = is_singular() ? $jsonld->post->post_author : get_the_author_meta( 'ID' );
		$author_name         = get_the_author();
		$author_url          = get_author_posts_url( $author_id );
		$data['ProfilePage'] = [
			'@type'       => 'Person',
			'@id'         => ! $is_archive_disabled ? $author_url : $jsonld->parts['url'] . '#author',
			'name'        => $author_name,
			'description' => wp_strip_all_tags( stripslashes( $this->get_description( $author_id ) ), true ),
			'url'         => $is_archive_disabled ? '' : $author_url,
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
	 * Add sameAs property to the Person entity.
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
	 * Add image property to the Person entity.
	 *
	 * @param array  $entity    Author schema data.
	 * @param int    $author_id Author ID.
	 * @param JsonLD $jsonld    JsonLD Instance.
	 */
	private function add_image( &$entity, $author_id, $jsonld ) {
		$entity['image'] = [
			'@type'   => 'ImageObject',
			'@id'     => get_avatar_url( $author_id ),
			'url'     => get_avatar_url( $author_id ),
			'caption' => get_the_author(),
		];

		$jsonld->add_prop( 'language', $entity['image'] );
	}

	/**
	 * Add worksFor property referencing it to the publisher entity.
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

	/**
	 * Get author description.
	 *
	 * @param int $author_id Author ID.
	 */
	private function get_description( $author_id ) {
		$description = User::get_meta( 'description', $author_id );
		return $description ? $description : Paper::get_from_options( 'author_archive_description' );
	}
}
