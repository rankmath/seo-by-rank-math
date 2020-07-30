<?php
/**
 * The Article Class.
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
 * Article class.
 */
class Article implements Snippet {

	/**
	 * Article rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		if ( ! $type = Helper::get_post_meta( 'snippet_article_type' ) ) { // phpcs:ignore
			$type = Helper::get_settings( "titles.pt_{$jsonld->post->post_type}_default_article_type" );
		}

		$entity = [
			'@type'            => $type,
			'headline'         => $jsonld->parts['title'],
			'datePublished'    => $jsonld->parts['published'],
			'dateModified'     => $jsonld->parts['modified'],
			'publisher'        => $jsonld->get_publisher( $data ),
			'mainEntityOfPage' => [
				'@type' => 'WebPage',
				'@id'   => $jsonld->parts['canonical'],
			],
			'author'           => [
				'@type' => 'Person',
				'name'  => $jsonld->parts['author'],
			],
		];

		if ( ! empty( $jsonld->parts['desc'] ) ) {
			$entity['description'] = $jsonld->parts['desc'];
		}

		if ( isset( $data['Organization'] ) ) {
			unset( $data['Organization'] );
		}

		return $entity;
	}
}
