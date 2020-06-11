<?php
/**
 * The Review Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\RichSnippet
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\RichSnippet;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Review class.
 */
class Review implements Snippet {

	use Hooker;

	/**
	 * Review rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$entity = [
			'@type'         => 'Review',
			'author'        => [
				'@type' => 'Person',
				'name'  => $jsonld->parts['author'],
			],
			'name'          => $jsonld->parts['title'],
			'datePublished' => $jsonld->parts['published'],
			'description'   => $jsonld->parts['desc'],
			'itemReviewed'  => [
				'@type' => 'Thing',
				'name'  => $jsonld->parts['title'],
			],
			'reviewRating'  => [
				'@type'       => 'Rating',
				'worstRating' => Helper::get_post_meta( 'snippet_review_worst_rating' ),
				'bestRating'  => Helper::get_post_meta( 'snippet_review_best_rating' ),
				'ratingValue' => Helper::get_post_meta( 'snippet_review_rating_value' ),
			],
		];

		$jsonld->add_prop( 'thumbnail', $entity['itemReviewed'] );

		return $entity;
	}
}
