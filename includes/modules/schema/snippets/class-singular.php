<?php
/**
 * The Singular Class.
 *
 * @since      1.0.13
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Schema\DB;

defined( 'ABSPATH' ) || exit;

/**
 * Singular class.
 */
class Singular implements Snippet {

	use Hooker;

	/**
	 * Generate rich snippet.
	 *
	 * @param array  $data   Array of JSON-LD data.
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return array
	 */
	public function process( $data, $jsonld ) {
		$schema = $this->can_add_schema( $jsonld );
		if ( false === $schema ) {
			return $data;
		}

		$hook = 'snippet/rich_snippet_' . $schema;
		/**
		 * Short-circuit if 3rd party is interested generating his own data.
		 */
		$pre = $this->do_filter( $hook, false, $jsonld->parts, $data );
		if ( false !== $pre ) {
			$data['richSnippet'] = $this->do_filter( $hook . '_entity', $pre );
			return $data;
		}

		$object = $this->get_schema_class( $schema );
		if ( false === $object ) {
			return $data;
		}

		$entity = $object->process( $data, $jsonld );

		$data['richSnippet'] = $this->do_filter( $hook . '_entity', $entity );

		return $data;
	}

	/**
	 * Get Schema type.
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return boolean|string
	 */
	private function can_add_schema( $jsonld ) {
		if ( empty( $jsonld->post_id ) ) {
			return false;
		}

		$schemas = DB::get_schemas( $jsonld->post_id );
		if ( ! empty( $schemas ) ) {
			$has_product = array_filter(
				$schemas,
				function( $schema ) {
					return ! empty( $schema['@type'] ) && in_array( $schema['@type'], [ 'WooCommerceProduct', 'EDDProduct' ], true );
				}
			);
			return ! empty( $has_product ) ? 'product' : false;
		}

		if ( metadata_exists( 'post', $jsonld->post_id, 'rank_math_rich_snippet' ) ) {
			return Helper::get_post_meta( 'rich_snippet' );
		}

		if ( ! Helper::can_use_default_schema( $jsonld->post_id ) ) {
			return false;
		}

		return $this->get_default_schema( $jsonld );
	}

	/**
	 * Get Default Rich Snippet type from Settings.
	 *
	 * @param JsonLD $jsonld JsonLD Instance.
	 *
	 * @return string
	 */
	private function get_default_schema( $jsonld ) {
		$schema = Helper::get_default_schema_type( $jsonld->post_id, true );
		if ( ! $schema ) {
			return false;
		}

		if ( in_array( $schema, [ 'BlogPosting', 'NewsArticle', 'Article' ], true ) ) {
			return 'article';
		}

		if (
			( Helper::is_woocommerce_active() && is_singular( 'product' ) ) ||
			( Helper::is_edd_active() && is_singular( 'download' ) )
		) {
			return 'product';
		}

		return false;
	}

	/**
	 * Get appropriate Schema Class.
	 *
	 * @param string $schema Schema type.
	 * @return bool|Class
	 */
	private function get_schema_class( $schema ) {
		$data = [
			'article' => '\\RankMath\\Schema\\Article',
			'product' => '\\RankMath\\Schema\\Product',
		];

		if ( isset( $data[ $schema ] ) && class_exists( $data[ $schema ] ) ) {
			return new $data[ $schema ]();
		}

		return false;
	}
}
