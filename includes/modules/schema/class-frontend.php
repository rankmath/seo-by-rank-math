<?php
/**
 * The frontend code of the Schema module.
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
		if ( Helper::is_divi_frontend_editor() ) {
			return;
		}
		$this->action( 'rank_math/json_ld', 'add_schema', 10, 2 );
		$this->action( 'rank_math/json_ld', 'connect_schema_entities', 99, 2 );
		$this->filter( 'rank_math/snippet/rich_snippet_event_entity', 'validate_event_schema', 11, 2 );
		$this->filter( 'rank_math/snippet/rich_snippet_article_entity', 'add_name_property', 11, 2 );

		new Opengraph();
	}

	/**
	 * Output schema data for a post.
	 *
	 * @param array  $data   Array of json-ld data.
	 * @param JsonLD $jsonld Instance of jsonld.
	 *
	 * @return array
	 */
	public function add_schema( $data, $jsonld ) {
		if ( ! is_singular() || post_password_required() ) {
			return $data;
		}

		global $post;
		$schemas = array_filter(
			DB::get_schemas( $post->ID ),
			function( $schema ) {
				return ! in_array( $schema['@type'], [ 'WooCommerceProduct', 'EDDProduct' ], true );
			}
		);

		// Check & Unpublish the JobPosting post.
		DB::unpublish_jobposting_post( $jsonld, $schemas );

		$schemas = $jsonld->replace_variables( $schemas, [], $data );
		$schemas = $jsonld->filter( $schemas, $jsonld, $data );

		return array_merge( $data, $schemas );
	}

	/**
	 * Connect different schema entities using isPartOf & publisher properties.
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

		$jsonld->parts['canonical'] = ! empty( $jsonld->parts['canonical'] ) ? $jsonld->parts['canonical'] : \RankMath\Paper\Paper::get()->get_canonical();

		$schema_types = [];
		foreach ( $schemas as $id => $schema ) {
			if ( ! Str::starts_with( 'schema-', $id ) && 'richSnippet' !== $id ) {
				continue;
			}

			$schema_types[] = $schema['@type'];
			$this->connect_properties( $schema, $id, $jsonld, $schemas );
			$this->add_main_entity_of_page( $schema, $jsonld );
			$schemas[ $id ] = $schema;
		}

		return $this->change_webpage_entity( $schemas, $schema_types );
	}

	/**
	 * Add name property to the Article schema.
	 *
	 * @since 1.0.61
	 *
	 * @param  array $schema Snippet Data.
	 * @return array
	 */
	public function add_name_property( $schema ) {
		if ( empty( $schema['headline'] ) ) {
			return $schema;
		}

		$schema['name'] = $schema['headline'];
		return $schema;
	}

	/**
	 * Add timezone to startDate field.
	 *
	 * @param array $schema Event schema Data.
	 * @return array
	 */
	public function validate_event_schema( $schema ) {
		if ( empty( $schema['startDate'] ) ) {
			return $schema;
		}

		$schema['startDate'] = str_replace( ' ', 'T', Helper::convert_date( $schema['startDate'], true ) );

		return $schema;
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
		if ( isset( $schema['isCustom'] ) ) {
			unset( $schema['isCustom'] );
			return;
		}

		// Remove empty ImageObject.
		if ( isset( $schema['image'] ) && empty( $schema['image']['url'] ) ) {
			unset( $schema['image'] );
		}

		$jsonld->parts['canonical'] = ! empty( $jsonld->parts['canonical'] ) ? $jsonld->parts['canonical'] : \RankMath\Paper\Paper::get()->get_canonical();
		$schema['@id']              = $jsonld->parts['canonical'] . '#' . $id;

		$types = array_map( 'strtolower', (array) $schema['@type'] );
		foreach ( $types as $type ) {
			$is_event = Str::contains( 'event', $type );
			// Add publisher entity @id in the organizer property of Event schema.
			if ( $is_event ) {
				$jsonld->add_prop( 'publisher', $schema, 'organizer', $schemas );
			}

			$props = [
				'is_part_of' => [
					'key'   => 'webpage',
					'value' => ! in_array( $type, [ 'jobposting', 'musicgroup', 'person', 'product', 'restaurant', 'service' ], true ) && ! $is_event,
				],
				'publisher'  => [
					'key'   => 'publisher',
					'value' => ! in_array( $type, [ 'jobposting', 'musicgroup', 'person', 'product', 'restaurant', 'service' ], true ) && ! $is_event,
				],
				'thumbnail'  => [
					'key'   => 'image',
					'value' => ! in_array( $type, [ 'videoobject' ], true ) || isset( $schema['image'] ),
				],
				'language'   => [
					'key'   => 'inLanguage',
					'value' => ! in_array( $type, [ 'person', 'service', 'restaurant', 'product', 'musicgroup', 'musicalbum', 'jobposting' ], true ),
				],
			];

			foreach ( $props as $prop => $data ) {
				if ( ! $data['value'] ) {
					continue;
				}

				$jsonld->add_prop( $prop, $schema, $data['key'], $schemas );
			}
		}
	}

	/**
	 * Add mainEntityOfPage property to Primary schema entity.
	 *
	 * @param array  $schema Schema Entity.
	 * @param JsonLD $jsonld JsonLD Instance.
	 */
	private function add_main_entity_of_page( &$schema, $jsonld ) {
		if ( ! isset( $schema['isPrimary'] ) ) {
			return;
		}

		if ( ! empty( $schema['isPrimary'] ) ) {
			$schema['mainEntityOfPage'] = [ '@id' => $jsonld->parts['canonical'] . '#webpage' ];
		}

		unset( $schema['isPrimary'] );
	}

	/**
	 * Change WebPage entity type depending on the schemas on the page.
	 *
	 * @param array $schemas Schema data.
	 * @param array $types   Schema types.
	 *
	 * @return array
	 */
	private function change_webpage_entity( $schemas, $types ) {
		if ( in_array( 'Product', $types, true ) ) {
			$schemas['WebPage']['@type'] = 'ItemPage';
		}

		if ( isset( $schemas['howto'] ) && ! empty( $schemas['WebPage'] ) ) {
			$schemas['howto']['mainEntityOfPage'] = [ '@id' => $schemas['WebPage']['@id'] ];
		}

		$faq_data = array_map(
			function( $schema ) {
				return isset( $schema['@type'] ) && 'FAQPage' === $schema['@type'];
			},
			$schemas
		);

		$faq_key = ! empty( $faq_data ) ? key( array_filter( $faq_data ) ) : '';
		if ( ! $faq_key ) {
			return $schemas;
		}

		if ( in_array( $faq_key, array_keys( $schemas ), true ) ) {
			$schemas['WebPage']['@type'] =
				! empty( $types )
					? [
						$schemas['WebPage']['@type'],
						'FAQPage',
					]
					: 'FAQPage';

			$schemas['WebPage']['mainEntity'] = $schemas[ $faq_key ]['mainEntity'];

			unset( $schemas[ $faq_key ] );
		}

		return $schemas;
	}
}
