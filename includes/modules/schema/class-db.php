<?php
/**
 * The Schema module database operations.
 *
 * @since      1.4.3
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Database\Database;

defined( 'ABSPATH' ) || exit;

/**
 * DB class.
 */
class DB {

	/**
	 * Get query builder object.
	 *
	 * @param string $table Meta table name.
	 *
	 * @return Query_Builder
	 */
	private static function table( $table = 'postmeta' ) {
		return Database::table( $table );
	}

	/**
	 * Get all schemas by Object ID.
	 *
	 * @param int    $object_id  Object ID.
	 * @param string $table      Meta table name.
	 * @param bool   $from_db    If set to true, the schema will be retrieved from the database.
	 *
	 * @return array
	 */
	public static function get_schemas( $object_id, $table = 'postmeta', $from_db = false ) {
		static $schema_cache = [];

		// Add exception handler.
		if ( is_null( $object_id ) ) {
			return [];
		}

		// Get from cache.
		if ( ! $from_db && isset( $schema_cache[ $table . '_' . $object_id ] ) ) {
			return $schema_cache[ $table . '_' . $object_id ];
		}

		$key  = 'termmeta' === $table ? 'term_id' : 'post_id';
		$data = self::table( $table )
			->select( 'meta_id' )
			->select( 'meta_value' )
			->where( $key, $object_id )
			->whereLike( 'meta_key', 'rank_math_schema', '' )
			->get();

		$schemas = [];
		foreach ( $data as $schema ) {
			$value = maybe_unserialize( $schema->meta_value );
			if ( empty( $value ) ) {
				continue;
			}

			$id             = 'schema-' . $schema->meta_id;
			$schemas[ $id ] = maybe_unserialize( $schema->meta_value );
		}

		// Add to cache.
		$schema_cache[ $table . '_' . $object_id ] = $schemas;

		return $schemas;
	}

	/**
	 * Get Schema types by Object ID.
	 *
	 * @param int  $object_id Object ID.
	 * @param bool $sanitize  Sanitize schema types.
	 * @param bool $translate Whether to get the schema name.
	 *
	 * @return array
	 */
	public static function get_schema_types( $object_id, $sanitize = false, $translate = true ) {
		$schemas = self::get_schemas( $object_id );

		if ( empty( $schemas ) && Helper::get_default_schema_type( $object_id ) ) {
			$schemas[] = [ '@type' => ucfirst( Helper::get_default_schema_type( $object_id ) ) ];
		}

		if ( has_block( 'rank-math/faq-block', $object_id ) ) {
			$schemas[] = [ '@type' => 'FAQPage' ];
		}

		if ( has_block( 'rank-math/howto-block', $object_id ) ) {
			$schemas[] = [ '@type' => 'HowTo' ];
		}

		if ( empty( $schemas ) ) {
			return false;
		}

		$types = array_reduce(
			wp_list_pluck( $schemas, '@type' ),
			function( $carry, $type ) {
				if ( is_array( $type ) ) {
					return array_merge( $carry, $type );
				}

				$carry[] = $type;
				return $carry;
			},
			[]
		);

		$types = array_unique( $types );

		if ( $sanitize ) {
			$types = array_map(
				function ( $type ) use ( $translate ) {
					return Helper::sanitize_schema_title( $type, $translate );
				},
				$types
			);
		}
		return implode( ', ', $types );
	}

	/**
	 * Get schema by shortcode id.
	 *
	 * @param  string $id Shortcode unique id.
	 * @param  bool   $from_db If set to true, the schema will be retrieved from the database.
	 * @return array
	 */
	public static function get_schema_by_shortcode_id( $id, $from_db = false ) {
		/**
		 * Keep Schema data in memory after querying by shortcode ID, to avoid
		 * unnecessary queries.
		 *
		 * @var array
		 */
		static $cached_schema_shortcodes = [];

		if ( ! $from_db && isset( $cached_schema_shortcodes[ $id ] ) ) {
			return $cached_schema_shortcodes[ $id ];
		}

		// First, check for meta_key matches for a "shortcut" to the schema.
		$shortcut = false;
		if ( strpos( self::table()->table, 'post' ) !== false ) {
			// Only check for shortcuts if we're querying for a post.
			$shortcut = self::table()
				->select( 'meta_value' )
				->where( 'meta_key', 'rank_math_shortcode_schema_' . $id )
				->one();
		}

		if ( ! empty( $shortcut ) ) {
			$data = self::table()
				->select( 'post_id' )
				->select( 'meta_value' )
				->where( 'meta_id', $shortcut->meta_value )
				->one();

			if ( ! empty( $data ) ) {
				$schema = [
					'post_id' => $data->post_id,
					'schema'  => maybe_unserialize( $data->meta_value ),
				];

				// Cache the schema for future use.
				$cached_schema_shortcodes[ $id ] = $schema;

				return $schema;
			}
		}

		$data = self::table()
			->select( 'post_id' )
			->select( 'meta_value' )
			->whereLike( 'meta_value', $id, '%:"' )
			->one();

		if ( ! empty( $data ) ) {
			$schema = [
				'post_id' => $data->post_id,
				'schema'  => maybe_unserialize( $data->meta_value ),
			];

			// Cache the schema for future use.
			$cached_schema_shortcodes[ $id ] = $schema;

			return $schema;
		}

		return false;
	}

	/**
	 * Get schema type for template.
	 *
	 * @param int $post_id Post id.
	 *
	 * @return string
	 */
	public static function get_template_type( $post_id ) {
		$data = self::table()
			->select( 'meta_value' )
			->where( 'post_id', $post_id )
			->whereLike( 'meta_key', 'rank_math_schema', '' )
			->one();

		if ( empty( $data ) ) {
			return '';
		}

		$schema = maybe_unserialize( $data->meta_value );

		return [
			'type'   => isset( $schema['@type'] ) ? $schema['@type'] : '',
			'schema' => $schema,
		];
	}

	/**
	 * Delete Schema data using Post ID.
	 *
	 * @param int $post_id Post ID.
	 *
	 * @return string
	 */
	public static function delete_schema_data( $post_id ) {
		return self::table()->where( 'post_id', $post_id )->whereLike( 'meta_key', 'rank_math_schema_' )->delete();
	}

	/**
	 * Unpublish job posting when expired.
	 *
	 * @param JsonLD $jsonld  JsonLD Instance.
	 * @param array  $schemas Array of JSON-LD entity.
	 */
	public static function unpublish_jobposting_post( $jsonld, $schemas ) {
		if ( ! is_singular() ) {
			return;
		}

		$job_postings = array_map(
			function( $schema ) {
				return isset( $schema['@type'] ) && 'JobPosting' === $schema['@type'] ? $schema : false;
			},
			$schemas
		);

		if ( empty( $job_postings ) ) {
			return;
		}

		foreach ( $job_postings as $job_posting ) {
			if (
				empty( $job_posting['metadata']['unpublish'] ) ||
				'on' !== $job_posting['metadata']['unpublish'] ||
				empty( $job_posting['validThrough'] ) ||
				date_create( 'now' )->getTimestamp() < strtotime( $job_posting['validThrough'] )
			) {
				continue;
			}

			wp_update_post(
				[
					'ID'          => $jsonld->post_id,
					'post_status' => 'draft',
				]
			);

			break;
		}
	}
}
