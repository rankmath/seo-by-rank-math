<?php
/**
 * Outputs schema code specific for Google's JSON LD stuff
 *
 * @since      1.4.3
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Frontend class.
 */
class Frontend {

	use Hooker;

	/**
	 * Hold post object.
	 *
	 * @var WP_Post
	 */
	public $post = null;

	/**
	 * Hold post ID.
	 *
	 * @var ID
	 */
	public $post_id = 0;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/json_ld', 'add_schema', 10, 2 );
		$this->action( 'rank_math/json_ld', 'connect_schema_entities', 99, 2 );
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld Instance of jsonld.
	 *
	 * @return array
	 */
	public function add_schema( $data, $jsonld ) {
		if ( ! is_singular() ) {
			return $data;
		}

		global $post;
		$schemas = array_filter(
			DB::get_schemas( $post->ID ),
			function( $schema ) {
				return 'WooCommerceProduct' !== $schema['@type'];
			}
		);
		$schemas = $jsonld->replace_variables( $schemas );
		$schemas = $jsonld->filter( $schemas, $jsonld, $data );

		return array_merge( $data, $schemas );
	}

	/**
	 * Connect schema entities.
	 *
	 * @param array  $schemas Array of json-ld data.
	 * @param JsonLD $jsonld  Instance of jsonld.
	 *
	 * @return array
	 */
	public function connect_schema_entities( $schemas, $jsonld ) {
		if ( empty( $schemas ) ) {
			return $schemas;
		}

		$schema_types = [];
		foreach ( $schemas as $id => $schema ) {
			if ( ! Str::starts_with( 'schema-', $id ) && 'richSnippet' !== $id ) {
				continue;
			}

			$schema_types[] = $schema['@type'];
			$this->connect_properties( $schema, $id, $jsonld, $schemas );
			$schemas[ $id ] = $schema;
		}

		if (
			! empty( $schema['publisher'] ) &&
			'Person' === $schemas['publisher']['@type'] &&
			! empty( array_intersect( $schema_types, [ 'Article', 'BlogPosting', 'NewsArticle' ] ) )
		) {
			$schemas['publisher']['@type'] = [
				'Person',
				'Organization',
			];

			if ( ! empty( $schemas['publisher']['image'] ) ) {
				$schemas['publisher']['logo'] = $schemas['publisher']['image'];
			}
		}

		return $this->change_webpage_entity( $schemas, $schema_types );
	}

	/**
	 * Connect schema properties.
	 *
	 * @param array  $schema  Schema Entity.
	 * @param string $id      Schema Entity ID.
	 * @param JsonLD $jsonld  JsonLD Instance.
	 * @param array  $schemas Array of json-ld data.
	 */
	private function connect_properties( &$schema, $id, $jsonld, $schemas ) {
		if ( isset( $schema['image'] ) && empty( $schema['image']['url'] ) ) {
			unset( $schema['image'] );
		}

		$schema['@id']              = $jsonld->parts['canonical'] . '#' . $id;
		$schema['mainEntityOfPage'] = [ '@id' => $jsonld->parts['canonical'] . '#webpage' ];

		$type  = \strtolower( $schema['@type'] );
		$props = [
			'is_part_of' => [
				'key'   => 'webpage',
				'value' => ! in_array( $type, [ 'jobposting', 'musicgroup', 'person', 'product', 'restaurant', 'service' ], true ) && ! Str::contains( 'event', $type ),
			],
			'publisher'  => [
				'key'   => 'publisher',
				'value' => ! in_array( $type, [ 'jobposting', 'musicgroup', 'person', 'product', 'restaurant', 'service' ], true ) && ! Str::contains( 'event', $type ),
			],
			'thumbnail'  => [
				'key'   => 'image',
				'value' => ! in_array( $type, [ 'videoobject' ], true ) || isset( $schema['image'] ),
			],
			'language'   => [
				'key'   => 'inLanguage',
				'value' => true,
			],
		];

		foreach ( $props as $prop => $data ) {
			if ( ! $data['value'] ) {
				continue;
			}

			$jsonld->add_prop( $prop, $schema, $data['key'], $schemas );
		}
	}

	/**
	 * Change WebPage properties depending on the schemas.
	 *
	 * @param array $schemas Schema data.
	 * @param array $types   Schema types.
	 *
	 * @return array
	 */
	private function change_webpage_entity( $schemas, $types ) {
		if ( in_array( 'product', $types, true ) ) {
			$schemas['WebPage']['@type'] = 'ItemPage';
		}

		if ( in_array( 'faqs', array_keys( $schemas ), true ) ) {
			$schemas['WebPage']['@type'] =
				! empty( $types )
					? [
						$schemas['WebPage']['@type'],
						'FAQPage',
					]
					: 'FAQPage';

			$schemas['WebPage']['mainEntity'] = $schemas['faqs']['mainEntity'];

			unset( $schemas['faqs'] );
		}

		return $schemas;
	}
}
