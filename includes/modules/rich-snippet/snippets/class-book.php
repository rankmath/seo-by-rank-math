<?php
/**
 * The Book Class.
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
 * Book class.
 */
class Book implements Snippet {

	/**
	 * Book rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type'   => 'Book',
			'name'    => $jsonld->parts['title'],
			'author'  => $this->get_author_parts( $jsonld->parts['author'] ),
			'url'     => $jsonld->parts['url'],
			'hasPart' => [],
		];

		$jsonld->add_ratings( 'book', $entity );
		foreach ( $this->get_editions() as $edition ) {
			$entity['hasPart'][] = $this->get_work_entity( $edition );
		}

		return $entity;
	}

	/**
	 * Get authors.
	 *
	 * @param string $authors Comma seperated author string.
	 *
	 * @return array
	 */
	private function get_author_parts( $authors ) {
		$parts   = [];
		$authors = explode( ', ', $authors );
		$authors = array_map( 'trim', $authors );

		foreach ( $authors as $author ) {
			$parts[] = [
				'@type' => 'Person',
				'name'  => $author,
			];
		}

		return $parts;
	}

	/**
	 * Get work entity.
	 *
	 * @param  array $edition Edition data.
	 * @return array
	 */
	private function get_work_entity( $edition ) {
		$work = [
			'@type'         => 'Book',
			'bookEdition'   => isset( $edition['book_edition'] ) ? $edition['book_edition'] : '',
			'bookFormat'    => 'https://schema.org/' . $edition['book_format'],
			'datePublished' => isset( $edition['date_published'] ) ? $edition['date_published'] : '',
		];

		$fields = [ 'isbn', 'name', 'author', 'url' ];
		foreach ( $fields as $field ) {
			if ( ! empty( $edition[ $field ] ) ) {
				$work[ $field ] = 'author' === $field ? $this->get_author_parts( $edition[ $field ] ) : $edition[ $field ];
			}
		}

		if ( ! empty( $edition['url'] ) ) {
			$work['@id'] = $edition['url'];
		}

		return $work;
	}

	/**
	 * Get book editions.
	 *
	 * @return array
	 */
	private function get_editions() {
		$editions = (array) Helper::get_post_meta( 'snippet_book_editions' );
		$editions = array_filter( $editions, [ $this, 'filter_edition' ], ARRAY_FILTER_USE_BOTH );
		return array_reverse( $editions );
	}

	/**
	 * Filter edition.
	 *
	 * @param  array $edition Array of edition.
	 * @param  mixed $key     Key of edition.
	 * @return boolean
	 */
	protected function filter_edition( $edition, $key ) {
		return ! ( empty( $edition ) || empty( $edition['name'] ) );
	}
}
