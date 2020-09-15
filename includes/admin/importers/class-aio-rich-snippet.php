<?php
/**
 * The AIO Schema Rich Snippets Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\DB;
use RankMath\Schema\JsonLD;
use RankMath\Schema\Singular;

defined( 'ABSPATH' ) || exit;

/**
 * Import_AIO_Rich_Snippet class.
 */
class AIO_Rich_Snippet extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'AIO Schema Rich Snippet';

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key = '_bsf_post_type';

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys = [ 'bsf_', 'bsf_%' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'postmeta' ];

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
	 * Import post meta of plugin.
	 *
	 * @return array
	 */
	protected function postmeta() {
		$this->set_pagination( $this->get_post_ids( true ) );

		// Set Converter.
		$this->json_ld = new JsonLD();
		$this->single  = new Singular();

		foreach ( $this->get_post_ids() as $snippet_post ) {
			$type      = $this->is_allowed_type( $snippet_post->meta_value );
			$meta_keys = $this->get_metakeys( $type );
			if ( false === $type || false === $meta_keys ) {
				continue;
			}

			$this->set_postmeta( $snippet_post->post_id, $type, $meta_keys );
			update_post_meta( $snippet_post->post_id, 'rank_math_rich_snippet', $type );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Set snippet meta.
	 *
	 * @param int    $post_id   Post ID.
	 * @param string $type      Type to get keys for.
	 * @param array  $meta_keys Array of meta keys to save.
	 */
	private function set_postmeta( $post_id, $type, $meta_keys ) {

		foreach ( $meta_keys as $snippet_key => $snippet_value ) {
			$value = get_post_meta( $post_id, '_bsf_' . $snippet_key, true );
			$value = in_array( $snippet_key, [ 'event_start_date', 'event_end_date' ], true ) ? strtotime( $value ) : $value;
			if ( $this->has_address( $type, $snippet_key ) ) {
				$address[ $snippet_value ] = $value;
				$value                     = $address;
				$snippet_value             = "{$type}_address";
			}

			update_post_meta( $post_id, 'rank_math_snippet_' . $snippet_value, $value );
		}
		// Convert post now.
		$data = $this->json_ld->get_old_schema( $post_id, $this->single );
		if ( isset( $data['richSnippet'] ) ) {
			$data             = $data['richSnippet'];
			$type             = $data['@type'];
			$data['metadata'] = [
				'title' => $type,
				'type'  => 'template',
			];
			update_post_meta( $post_id, 'rank_math_schema_' . $type, $data );
		}
	}

	/**
	 * Check if the snippet has address field.
	 *
	 * @param string $type        Snippet type.
	 * @param string $snippet_key Snippet meta key.
	 *
	 * @return bool
	 */
	private function has_address( $type, $snippet_key ) {
		$event_array  = [ 'event_organization', 'event_street', 'event_local', 'event_region', 'event_postal_code' ];
		$person_array = [ 'people_street', 'people_local', 'people_local', 'people_region', 'people_postal' ];

		return ( 'event' === $type && in_array( $snippet_key, $event_array, true ) ) ||
			( 'person' === $type && in_array( $snippet_key, $person_array, true ) );
	}

	/**
	 * Get the actions which can be performed for the plugin.
	 *
	 * @return array
	 */
	public function get_choices() {
		return [
			'postmeta' => esc_html__( 'Import Schemas', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import all Schema data for Posts, Pages, and custom post types.', 'rank-math' ) ),
		];
	}

	/**
	 * Get all post IDs of all allowed post types only.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	protected function get_post_ids( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'postmeta' )->where( 'meta_key', '_bsf_post_type' );

		return $count ? absint( $table->selectCount( 'meta_id' )->getVar() ) :
			$table->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Get snippet types.
	 *
	 * @return array
	 */
	private function get_types() {
		return [
			'1'  => 'review',
			'2'  => 'event',
			'5'  => 'person',
			'6'  => 'product',
			'7'  => 'recipe',
			'8'  => 'software',
			'9'  => 'video',
			'10' => 'article',
			'11' => 'service',
		];
	}

	/**
	 * Is snippet type allowed.
	 *
	 * @param string $type Type to check.
	 *
	 * @return bool
	 */
	private function is_allowed_type( $type ) {
		$types = $this->get_types();
		return isset( $types[ $type ] ) ? $types[ $type ] : false;
	}

	/**
	 * Get meta keys hash to import.
	 *
	 * @param string $type Type to get keys for.
	 *
	 * @return array
	 */
	private function get_metakeys( $type ) {
		$hash = [
			'event'    => $this->get_event_fields(),
			'product'  => $this->get_product_fields(),
			'recipe'   => $this->get_recipe_fields(),
			'software' => $this->get_software_fields(),
			'video'    => $this->get_video_fields(),
			'article'  => [
				'article_name' => 'name',
				'article_desc' => 'desc',
			],
			'person'   => [
				'people_fn'        => 'name',
				'people_nickname'  => 'desc',
				'people_job_title' => 'job_title',
				'people_street'    => 'streetAddress',
				'people_local'     => 'addressLocality',
				'people_region'    => 'addressRegion',
				'people_postal'    => 'postalCode',
			],
			'review'   => [
				'item_reviewer' => 'name',
				'item_name'     => 'desc',
				'rating'        => 'review_rating_value',
			],
			'service'  => [
				'service_type' => 'service_type',
				'service_desc' => 'desc',
			],
		];

		return isset( $hash[ $type ] ) ? $hash[ $type ] : false;
	}

	/**
	 * Get event fields.
	 *
	 * @return array
	 */
	private function get_event_fields() {
		return [
			'event_title'        => 'name',
			'event_organization' => 'addressCountry',
			'event_street'       => 'streetAddress',
			'event_local'        => 'addressLocality',
			'event_region'       => 'addressRegion',
			'event_postal_code'  => 'postalCode',
			'event_desc'         => 'desc',
			'event_start_date'   => 'event_startdate',
			'event_end_date'     => 'event_enddate',
			'event_price'        => 'event_price',
			'event_cur'          => 'event_currency',
			'event_ticket_url'   => 'event_ticketurl',
		];
	}

	/**
	 * Get product fields.
	 *
	 * @return array
	 */
	private function get_product_fields() {
		return [
			'product_brand' => 'product_brand',
			'product_name'  => 'name',
			'product_price' => 'product_price',
			'product_cur'   => 'product_currency',
		];
	}

	/**
	 * Get recipe fields.
	 *
	 * @return array
	 */
	private function get_recipe_fields() {
		return [
			'recipes_name'       => 'name',
			'recipes_preptime'   => 'recipe_preptime',
			'recipes_cooktime'   => 'recipe_cooktime',
			'recipes_totaltime'  => 'recipe_totaltime',
			'recipes_desc'       => 'desc',
			'recipes_ingredient' => 'recipe_ingredients',
		];
	}

	/**
	 * Get software fields.
	 *
	 * @return array
	 */
	private function get_software_fields() {
		return [
			'software_rating' => 'software_rating_value',
			'software_price'  => 'software_price',
			'software_cur'    => 'software_price_currency',
			'software_name'   => 'name',
			'software_os'     => 'software_operating_system',
			'software_cat'    => 'software_application_category',
		];
	}

	/**
	 * Get video fields.
	 *
	 * @return array
	 */
	private function get_video_fields() {
		return [
			'video_title'    => 'name',
			'video_desc'     => 'desc',
			'video_thumb'    => 'rank_math_twitter_title',
			'video_url'      => 'video_url',
			'video_emb_url'  => 'video_embed_url',
			'video_duration' => 'video_duration',
		];
	}
}
