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

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\DB;
use MyThemeShop\Helpers\Str;

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
	 * Import post meta of plugin.
	 *
	 * @return array
	 */
	protected function postmeta() {
		$this->set_pagination( $this->get_post_ids( true ) );

		// Set Converter.
		foreach ( $this->get_post_ids() as $snippet_post ) {
			$type      = $this->is_allowed_type( $snippet_post->meta_value );
			$meta_keys = $this->get_metakeys( $type );
			if ( false === $type || false === $meta_keys ) {
				continue;
			}

			$this->set_postmeta( $snippet_post->post_id, $type, $meta_keys );
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
		$data = [];
		foreach ( $meta_keys as $snippet_key => $snippet_value ) {
			$value = get_post_meta( $post_id, '_bsf_' . $snippet_key, true );
			$this->validate_schema_data( $data, $value, $snippet_value );
		}

		if ( empty( $data ) ) {
			return;
		}

		// Convert post now.
		$data['@type']    = $this->validate_type( $type );
		$data['metadata'] = [
			'title'     => Helper::sanitize_schema_title( $data['@type'] ),
			'type'      => 'template',
			'isPrimary' => 1,
			'shortcode' => uniqid( 's-' ),
		];

		update_post_meta( $post_id, 'rank_math_schema_' . $data['@type'], $data );
	}

	/**
	 * Validate schema type.
	 *
	 * @param string $type Schema Type.
	 */
	private function validate_type( $type ) {
		if ( 'software' === $type ) {
			return 'SoftwareApplication';
		}

		if ( 'video' === $type ) {
			return 'VideoObject';
		}

		return ucfirst( $type );
	}

	/**
	 * Validate schema data.
	 *
	 * @param array  $data  Schema entity data.
	 * @param string $value Entity value.
	 * @param string $key   Entity key.
	 */
	private function validate_schema_data( &$data, $value, $key ) {
		if ( ! Str::contains( '.', $key ) ) {
			$data[ $key ] = $value;
			return;
		}

		$element = explode( '.', $key );
		if ( 2 === count( $element ) ) {
			$this->add_type( $data[ $element[0] ], $element[0] );
			$data[ $element[0] ][ $element[1] ] = $value;
			return;
		}

		if ( count( $element ) > 2 ) {
			$this->add_type( $data[ $element[0] ], $element[0] );
			$this->add_type( $data[ $element[0] ][ $element[1] ], $element[1] );
			$data[ $element[0] ][ $element[1] ][ $element[2] ] = $value;
		}
	}

	/**
	 * Add property type.
	 *
	 * @param array  $data Schema entity data.
	 * @param string $key  Entity key.
	 */
	private function add_type( &$data, $key ) {
		if ( 'location' === $key ) {
			$data['@type'] = 'Place';
		}

		if ( 'address' === $key ) {
			$data['@type'] = 'PostalAddress';
		}

		if ( 'offers' === $key ) {
			$data['@type'] = 'Offer';
		}

		if ( 'brand' === $key ) {
			$data['@type'] = 'Brand';
		}

		if ( 'review' === $key ) {
			$data['@type'] = 'Review';
		}

		if ( 'reviewRating' === $key ) {
			$data['@type'] = 'Rating';
		}

		if ( 'nutrition' === $key ) {
			$data['@type'] = 'NutritionInformation';
		}
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
				'article_name' => 'headline',
				'article_desc' => 'description',
			],
			'person'   => [
				'people_fn'        => 'name',
				'people_nickname'  => 'description',
				'people_job_title' => 'jobTitle',
				'people_street'    => 'address.streetAddress',
				'people_local'     => 'address.addressLocality',
				'people_region'    => 'address.addressRegion',
				'people_postal'    => 'address.postalCode',
			],
			'service'  => [
				'service_type' => 'serviceType',
				'service_desc' => 'description',
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
			'event_desc'         => 'description',
			'event_organization' => 'location.name',
			'event_street'       => 'location.address.streetAddress',
			'event_local'        => 'location.address.addressLocality',
			'event_region'       => 'location.address.addressRegion',
			'event_postal_code'  => 'location.address.postalCode',
			'event_start_date'   => 'startDate',
			'event_end_date'     => 'endDate',
			'event_price'        => 'offers.price',
			'event_cur'          => 'offers.priceCurrency',
			'event_ticket_url'   => 'offers.url',
		];
	}

	/**
	 * Get product fields.
	 *
	 * @return array
	 */
	private function get_product_fields() {
		return [
			'product_brand'  => 'brand.name',
			'product_name'   => 'name',
			'product_price'  => 'offers.price',
			'product_cur'    => 'offers.priceCurrency',
			'product_status' => 'offers.availability',
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
			'recipes_desc'       => 'description',
			'recipes_preptime'   => 'prepTime',
			'recipes_cooktime'   => 'cookTime',
			'recipes_totaltime'  => 'totalTime',
			'recipes_ingredient' => 'recipeIngredient',
			'recipes_nutrition'  => 'nutrition.calories',
		];
	}

	/**
	 * Get software fields.
	 *
	 * @return array
	 */
	private function get_software_fields() {
		return [
			'software_rating' => 'review.reviewRating.ratingValue',
			'software_price'  => 'offers.price',
			'software_cur'    => 'offers.priceCurrency',
			'software_name'   => 'name',
			'software_os'     => 'operatingSystem',
			'software_cat'    => 'applicationCategory',
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
			'video_desc'     => 'description',
			'video_thumb'    => 'thumbnailUrl',
			'video_url'      => 'contentUrl',
			'video_emb_url'  => 'embedUrl',
			'video_duration' => 'duration',
		];
	}
}
