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
		$entity = [
			'@type'          => Helper::get_default_schema_type( $jsonld->post->ID ),
			'headline'       => $jsonld->parts['title'],
			'keywords'       => Helper::replace_vars( '%keywords%', $jsonld->post ),
			'datePublished'  => $jsonld->parts['published'],
			'dateModified'   => $jsonld->parts['modified'],
			'isPrimary'      => true,
			'articleSection' => Helper::replace_vars( '%primary_taxonomy_terms%', $jsonld->post ),
			'author'         => ! empty( $data['ProfilePage'] ) ?
				[
					'@id'  => $data['ProfilePage']['@id'],
					'name' => $jsonld->parts['author'],
				] :
				[
					'@type' => 'Person',
					'name'  => $jsonld->parts['author'],
				],
		];

		$jsonld->add_prop( 'publisher', $entity, 'publisher', $data );
		if ( ! empty( $jsonld->parts['desc'] ) ) {
			$entity['description'] = $jsonld->parts['desc'];
		}

		return $entity;
	}
}
