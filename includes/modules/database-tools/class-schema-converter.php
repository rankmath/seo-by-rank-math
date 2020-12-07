<?php
/**
 * The Schema Converter.
 *
 * @since      1.0.48
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Tools;

use RankMath\Helper;
use RankMath\Schema\JsonLD;
use RankMath\Schema\Singular;
use MyThemeShop\Database\Database;
use MyThemeShop\Helpers\Conditional;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Schema_Converter class.
 */
class Schema_Converter extends \WP_Background_Process {

	/**
	 * Action.
	 *
	 * @var string
	 */
	protected $action = 'convert_schema_posts';

	/**
	 * SchemaMap.
	 *
	 * @var schemaMap
	 */
	private $schema_map;

	/**
	 * JsonLD.
	 *
	 * @var JsonLD
	 */
	private $json_ld;

	/**
	 * Singular.
	 *
	 * @var Singular
	 */
	private $single;

	/**
	 * Main instance
	 *
	 * Ensure only one instance is loaded or can be loaded.
	 *
	 * @return Schema_Converter
	 */
	public static function get() {
		static $instance;

		if ( is_null( $instance ) && ! ( $instance instanceof Schema_Converter ) ) {
			$instance = new Schema_Converter();
		}

		return $instance;
	}

	/**
	 * Start creating batches.
	 *
	 * @param [type] $posts [description].
	 */
	public function start( $posts ) {
		$chunks = array_chunk( $posts, 10 );
		foreach ( $chunks as $chunk ) {
			$this->push_to_queue( $chunk );
		}

		$this->save()->dispatch();
	}

	/**
	 * Complete.
	 *
	 * Override if applicable, but ensure that the below actions are
	 * performed, or, call parent::complete().
	 */
	protected function complete() {
		$posts = get_option( 'rank_math_schema_conversion_posts' );
		delete_option( 'rank_math_schema_conversion_posts' );
		Helper::add_notification(
			sprintf( 'Rank Math: Converted %d posts successfully from old Schema data format to new.', $posts['count'] ),
			[
				'type'    => 'success',
				'id'      => 'rank_math_schema_conversion_posts',
				'classes' => 'rank-math-notice',
			]
		);

		parent::complete();
	}

	/**
	 * Task to perform
	 *
	 * @param string $posts Posts to process.
	 *
	 * @return bool
	 */
	protected function task( $posts ) {
		global $wp_query;
		try {
			$this->json_ld = new JsonLD();
			$this->single  = new Singular();
			$this->get_schema_map();
			foreach ( $posts as $post_id ) {
				$this->convert( $post_id );
			}
			return false;
		} catch ( Exception $error ) {
			return true;
		}
	}

	/**
	 * Load schema map
	 */
	private function get_schema_map() {
		ob_start();
		include rank_math()->plugin_dir() . 'assets/vendor/schema/schema-map.php';
		$data             = ob_get_clean();
		$data             = json_decode( $data, true );
		$this->schema_map = $data['schemas'];
	}

	/**
	 * Convert post.
	 *
	 * @param int $post_id Post id to convert.
	 */
	public function convert( $post_id ) {
		$data = $this->json_ld->get_old_schema( $post_id, $this->single );
		if ( isset( $data['richSnippet'] ) ) {
			$data             = $this->map_fields( $data['richSnippet'], $post_id );
			$type             = $this->sanitize_type( $data['@type'] );
			$data['metadata'] = $this->get_metadata( $post_id, $type );
			update_post_meta( $post_id, 'rank_math_schema_' . $type, $data );
		}
	}

	/**
	 * Convert post metadata.
	 *
	 * @param int    $post_id Post id to convert.
	 * @param string $type    Schema type.
	 *
	 * @return array Return metadata.
	 */
	private function get_metadata( $post_id, $type ) {
		$metadata = [
			'title'     => $type,
			'isPrimary' => true,
			'type'      => 'template',
		];

		$review_location = get_post_meta( $post_id, 'rank_math_snippet_location', true );
		if ( ! empty( $review_location ) ) {
			$metadata['reviewLocation'] = $review_location;
		}

		$unpublish = get_post_meta( $post_id, 'rank_math_snippet_jobposting_unpublish', true );
		if ( 'JobPosting' === $type && ! empty( $unpublish ) ) {
			$metadata['unpublish'] = $unpublish;
		}

		return $metadata;
	}

	/**
	 * Sanitize type.
	 *
	 * @param  string $type Schema Type.
	 * @return string
	 */
	private function sanitize_type( $type ) {
		if ( 'NewsArticle' === $type || 'BlogPosting' === $type ) {
			return 'Article';
		}

		if ( 'MusicGroup' === $type || 'MusicAlbum' === $type ) {
			return 'Music';
		}

		if ( Str::contains( 'Event', $type ) ) {
			return 'Event';
		}

		return $type;
	}

	/**
	 * Map Schema Fields before converting.
	 *
	 * @param array $schema_data Schema Data.
	 * @param int   $post_id Post id to convert.
	 */
	private function map_fields( $schema_data, $post_id ) {
		$post_type = get_post_type( $post_id );
		if ( Conditional::is_woocommerce_active() && 'product' === $post_type ) {
			return [ '@type' => 'WooCommerceProduct' ];
		}

		if ( Conditional::is_edd_active() && 'download' === $post_type ) {
			return [ '@type' => 'EDDProduct' ];
		}

		$defaults       = [
			'headline'    => '%seo_title%',
			'name'        => '%seo_title%',
			'title'       => '%seo_title%',
			'description' => '%seo_description%',
			'url'         => '%url%',
		];
		$date_variables = [
			'datePublished' => '%date(Y-m-d\TH:i:sP)%',
			'dateModified'  => '%modified(Y-m-d\TH:i:sP)%',
		];

		$type         = $schema_data['@type'];
		$type         = $this->sanitize_type( $type );
		$valid_keys   = array_keys( $this->schema_map[ $type ] );
		$valid_keys[] = 'recipeInstructions';

		foreach ( $schema_data as $key => $data ) {
			if ( '@type' !== $key && ! in_array( $key, $valid_keys, true ) ) {
				unset( $schema_data[ $key ] );
				continue;
			}

			if ( in_array( $key, array_keys( $date_variables ), true ) && in_array( $type, [ 'Article', 'Review', 'Recipe' ], true ) ) {
				$schema_data[ $key ] = $date_variables[ $key ];
			}

			if ( ! $data && in_array( $key, array_keys( $defaults ), true ) ) {
				$schema_data[ $key ] = $defaults[ $key ];
			}
			if ( 'Product' === $type && 'offers' === $key && empty( $data['url'] ) ) {
				$schema_data[ $key ]['url'] = '%url%';
			}
		}

		return $schema_data;
	}

	/**
	 * Find posts with yoast blocks.
	 *
	 * @return array
	 */
	public function find_posts() {
		global $wpdb;
		$posts = get_option( 'rank_math_schema_conversion_posts' );
		if ( false !== $posts ) {
			return $posts;
		}

		// Schema Posts.
		$posts = Database::table( 'postmeta' )
			->distinct()
			->select( 'post_id' )
			->whereLike( 'meta_key', 'rank_math_rich_snippet', '' )
			->get();

		$posts_data = [
			'posts' => [],
			'count' => 0,
		];

		if ( ! empty( $posts ) ) {
			$posts      = wp_list_pluck( $posts, 'post_id' );
			$posts_data = [
				'posts' => $posts,
				'count' => count( $posts ),
			];
		}
		update_option( 'rank_math_schema_conversion_posts', $posts_data );

		return $posts_data;
	}
}
