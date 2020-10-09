<?php
/**
 * The Article Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Article class.
 */
class Article implements Snippet {

	use Hooker;

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
			$type = Helper::get_default_schema_type( $jsonld->post->post_type );
		}

		$entity = [
			'@type'         => $type,
			'headline'      => $jsonld->parts['title'],
			'datePublished' => $jsonld->parts['published'],
			'dateModified'  => $jsonld->parts['modified'],
			'author'        => [
				'@type' => 'Person',
				'name'  => $jsonld->parts['author'],
			],
			'isPrimary'     => true,
		];

		$jsonld->add_prop( 'publisher', $entity, 'publisher', $data );
		if ( ! empty( $jsonld->parts['desc'] ) ) {
			$entity['description'] = $jsonld->parts['desc'];
		}

		return $entity;
	}
}
