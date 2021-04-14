<?php
/**
 * Output Opengraph tags for specific schema types.
 *
 * @since      1.0.56
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Opengraph class.
 */
class Opengraph {

	use Hooker;

	/**
	 * The Constructor.
	 */
	public function __construct() {
		$this->action( 'rank_math/opengraph/facebook', 'add_schema_tags', 90 );
	}

	/**
	 * Output the schema opengraph tags.
	 *
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	public function add_schema_tags( $opengraph ) {
		if ( ! is_singular() ) {
			return;
		}

		global $post;
		$schemas = array_filter(
			DB::get_schemas( $post->ID ),
			function( $schema ) {
				return in_array( $schema['@type'], [ 'Article', 'NewsArticle', 'BlogPosting', 'Product', 'VideoObject' ], true );
			}
		);
		if ( empty( $schemas ) ) {
			return;
		}

		$hash = [
			'VideoObject' => 'video',
			'Product'     => 'product',
			'Article'     => 'article',
			'NewsArticle' => 'article',
			'BlogPosting' => 'article',
		];

		foreach ( $schemas as $schema ) {
			$method = $hash[ $schema['@type'] ];
			$this->$method( $schema, $opengraph );
		}
	}

	/**
	 * Output Video Schema tags.
	 *
	 * @param array     $schema    Schema Data.
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	private function video( $schema, $opengraph ) {
		$video_url = ! empty( $schema['contentUrl'] ) ? $schema['contentUrl'] : ( ! empty( $schema['embedUrl'] ) ? $schema['embedUrl'] : '' );
		if ( ! $video_url ) {
			return;
		}

		$opengraph->tag( 'og:video', $video_url );
		if ( ! empty( $schema['duration'] ) ) {
			$opengraph->tag( 'video:duration', Helper::duration_to_seconds( $schema['duration'] ) );
		}
	}

	/**
	 * Output Product Schema tags.
	 *
	 * @param array     $schema    Schema Data.
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	public function product( $schema, $opengraph ) {
		if ( isset( $schema['brand'], $schema['brand']['name'] ) ) {
			$opengraph->tag( 'product:brand', $schema['brand']['name'] );
		}

		$tags = [
			'product:price:amount'   => ! empty( $schema['offers']['price'] ) ? $schema['offers']['price'] : '',
			'product:price:currency' => ! empty( $schema['offers']['priceCurrency'] ) ? $schema['offers']['priceCurrency'] : '',
			'product:availability'   => ! empty( $schema['offers']['availability'] ) && 'instock' === $schema['offers']['availability'] ? 'instock' : '',
		];

		foreach ( $tags as $tag => $value ) {
			$opengraph->tag( $tag, $value );
		}
	}

	/**
	 * Output Article Schema tags.
	 *
	 * @param array     $schema    Schema Data.
	 * @param OpenGraph $opengraph The current opengraph network object.
	 */
	private function article( $schema, $opengraph ) {
		if ( empty( $schema['datePublished'] ) || empty( $schema['dateModified'] ) ) {
			return;
		}

		global $post;
		$pub = Helper::replace_vars( $schema['datePublished'], $post );
		$mod = Helper::replace_vars( $schema['dateModified'], $post );
		$opengraph->tag( 'article:published_time', $pub );
		if ( $mod !== $pub ) {
			$opengraph->tag( 'article:modified_time', $mod );
		}
	}
}
