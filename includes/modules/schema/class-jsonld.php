<?php
/**
 * Outputs schema code specific for Google's JSON LD stuff
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Schema
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Schema;

use RankMath\Helper;
use RankMath\Paper\Paper;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\Conditional;
use MyThemeShop\Helpers\WordPress;
use MyThemeShop\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * JsonLD class.
 */
class JsonLD {

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
	 * Hold post parts.
	 *
	 * @var array
	 */
	public $parts = [];

	/**
	 * The Constructor.
	 */
	public function setup() {
		$this->action( 'rank_math/head', 'json_ld', 90 );
		$this->action( 'rank_math/json_ld', 'add_context_data' );
		$this->action( 'rank_math/json_ld/preview', 'generate_preview' );
		new Block_Parser();
		new Frontend();
	}

	/**
	 * Get Default Schema Data.
	 */
	public function generate_preview() {
		global $post;

		if ( is_singular() ) {
			$this->post    = $post;
			$this->post_id = $post->ID;
			$this->get_parts();
		}

		$data = $this->do_filter( 'json_ld', [], $this );
		unset( $data['BreadcrumbList'] );

		// Preview schema.
		$schema = \json_decode( file_get_contents( 'php://input' ), true );
		$schema = $this->replace_variables( $schema );
		$schema = $this->filter( $schema, $this, $data );

		if ( isset( $data[ $schema['schemaID'] ] ) ) {
			$current_data = $data[ $schema['schemaID'] ];
			unset( $data[ $schema['schemaID'] ] );
		} else {
			$current_data = array_pop( $data );
		}
		$schema = wp_parse_args( $schema['schema'], $current_data );
		// Merge.
		$data = array_merge( $data, [ 'schema' => $schema ] );
		$data = $this->validate_schema( $data );

		echo wp_json_encode( array_values( $data ) );
	}

	/**
	 * Get old schema for conversion.
	 *
	 * @param  int   $post_id Post id for conversion.
	 * @param  mixed $class   Class instance of snippet.
	 * @return array
	 */
	public function get_old_schema( $post_id, $class ) {
		global $post;
		$post          = get_post( $post_id ); // phpcs:ignore
		$this->post    = $post;
		$this->post_id = $post_id;
		setup_postdata( $post );
		$this->get_parts();

		/**
		 * Collect data to output in JSON-LD.
		 *
		 * @param array  $unsigned An array of data to output in json-ld.
		 * @param JsonLD $unsigned JsonLD instance.
		 */
		return $class->process( [], $this );
	}

	/**
	 * JSON LD output function that the functions for specific code can hook into.
	 */
	public function json_ld() {
		global $post;

		if ( is_singular() ) {
			$this->post    = $post;
			$this->post_id = $post->ID;
			$this->get_parts();
		}

		/**
		 * Collect data to output in JSON-LD.
		 *
		 * @param array  $unsigned An array of data to output in JSON-LD.
		 * @param JsonLD $unsigned JsonLD instance.
		 */
		$data = $this->do_filter( 'json_ld', [], $this );
		$data = $this->validate_schema( $data );
		if ( is_array( $data ) && ! empty( $data ) ) {

			$class = 'schema';

			if ( defined( 'RANK_MATH_PRO_FILE' ) ) {
				$class = 'schema-pro';
			}

			$json = [
				'@context' => 'https://schema.org',
				'@graph'   => array_values( $data ),
			];

			echo '<script type="application/ld+json" class="rank-math-' . esc_attr( $class ) . '">' . wp_json_encode( $json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES ) . '</script>' . "\n";
		}
	}

	/**
	 * Validate schema.
	 *
	 * @param array $data Array of JSON-LD data.
	 *
	 * @return array
	 */
	private function validate_schema( $data ) {
		foreach ( $data as $id => $value ) {
			if ( is_array( $value ) ) {
				// Remove aline @type.
				if ( isset( $value['@type'] ) && 1 === count( $value ) ) {
					unset( $data[ $id ] );
					continue;
				}

				// Remove empty review.
				if ( 'review' === $id && isset( $value['@type'] ) ) {
					if ( ! isset( $value['reviewRating'] ) || ! isset( $value['reviewRating']['ratingValue'] ) ) {
						unset( $data[ $id ] );
						continue;
					}
				}

				// Recursive.
				$data[ $id ] = $this->validate_schema( $value );
			}

			// Remove empty values.
			// Remove need of array_filter as this will go recursive.
			if ( '' === $value ) {
				unset( $data[ $id ] );
				continue;
			}
		}

		return $data;
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array $data Array of JSON-LD data.
	 *
	 * @return array
	 */
	public function add_schema( $data ) {
		global $post;

		$schema = DB::get_schemas( $post->ID );

		return array_merge( $data, $schema );
	}

	/**
	 * Get Default Schema Data.
	 *
	 * @param array $data Array of json-ld data.
	 *
	 * @return array
	 */
	public function add_context_data( $data ) {
		$is_product_page = $this->is_product_page();
		$snippets        = [
			'\\RankMath\\Schema\\Publisher'     => ! isset( $data['publisher'] ),
			'\\RankMath\\Schema\\Website'       => true,
			'\\RankMath\\Schema\\PrimaryImage'  => ! post_password_required() && is_singular(),
			'\\RankMath\\Schema\\Breadcrumbs'   => $this->can_add_breadcrumb(),
			'\\RankMath\\Schema\\Webpage'       => true,
			'\\RankMath\\Schema\\Author'        => is_author(),
			'\\RankMath\\Schema\\Products_Page' => $is_product_page,
			'\\RankMath\\Schema\\ItemListPage'  => ! $is_product_page && ( is_category() || is_tag() || is_tax() ),
			'\\RankMath\\Schema\\Singular'      => ! post_password_required() && is_singular(),
		];

		foreach ( $snippets as $class => $can_run ) {
			if ( $can_run ) {
				$class = new $class();
				$data  = $class->process( $data, $this );
			}
		}

		return $data;
	}

	/**
	 * Replace variable.
	 *
	 * @param  array $schemas Schema to replace.
	 * @return array
	 */
	public function replace_variables( $schemas ) {
		$new_schemas = [];

		foreach ( $schemas as $key => $schema ) {
			if ( 'metadata' === $key ) {
				$new_schemas['isPrimary'] = ! empty( $schema['isPrimary'] );
				continue;
			}

			if ( is_array( $schema ) ) {
				$new_schemas[ $key ] = $this->replace_variables( $schema );
				continue;
			}

			$new_schemas[ $key ] = Str::contains( '%', $schema ) ? Helper::replace_vars( $schema, get_queried_object() ) : $schema;

			if ( '' === $new_schemas[ $key ] ) {
				unset( $new_schemas[ $key ] );
			}
		}

		return $new_schemas;
	}

	/**
	 * Run schema filter.
	 *
	 * @param array  $schemas Schema to replace.
	 * @param JsonLD $jsonld  Instance of jsonld.
	 * @param array  $data   Array of json-ld data.
	 *
	 * @return array
	 */
	public function filter( $schemas, $jsonld, $data ) {
		$new_schemas = [];

		foreach ( $schemas as $key => $schema ) {
			$type = strtolower( $schema['@type'] );
			$type = in_array( $type, [ 'musicgroup', 'musicalbum' ], true )
				? 'music'
				: ( in_array( $type, [ 'blogposting', 'newsarticle' ], true ) ? 'article' : $type );
			$type = Str::contains( 'event', $type ) ? 'event' : $type;
			$hook = 'snippet/rich_snippet_' . $type;

			/**
			 * Short-circuit if 3rd party is interested generating his own data.
			 */
			$pre = $this->do_filter( $hook, false, $jsonld->parts, $data );
			if ( false !== $pre ) {
				$new_schemas[ $key ] = $this->do_filter( $hook . '_entity', $pre );
				continue;
			}

			$new_schemas[ $key ] = $this->do_filter( $hook . '_entity', $schema );
		}

		return $new_schemas;
	}

	/**
	 * Can add breadcrumb snippet.
	 *
	 * @return bool
	 */
	private function can_add_breadcrumb() {
		/**
		 * Allow developer to disable the breadcrumb JSON-LD output.
		 *
		 * @param bool $unsigned Default: true
		 */
		return ! is_front_page() && Helper::get_settings( 'general.breadcrumbs' ) && $this->do_filter( 'json_ld/breadcrumbs_enabled', true );
	}

	/**
	 * Is product page.
	 *
	 * @return bool
	 */
	private function is_product_page() {
		return Conditional::is_woocommerce_active() && ( ( is_tax() && in_array( get_query_var( 'taxonomy' ), get_object_taxonomies( 'product' ), true ) ) || is_shop() );
	}

	/**
	 * Add property to entity.
	 *
	 * @param string $prop   Name of the property to add into entity.
	 * @param array  $entity Array of json-ld entity.
	 * @param string $key    Property key to add into entity.
	 * @param array  $data   Array of json-ld data.
	 */
	public function add_prop( $prop, &$entity, $key = '', $data = [] ) {
		if ( empty( $prop ) ) {
			return;
		}

		$hash = [
			'email' => [ 'titles.email', 'email' ],
			'image' => [ 'titles.knowledgegraph_logo', 'logo' ],
			'phone' => [ 'titles.phone', 'telephone' ],
		];

		if ( isset( $hash[ $prop ] ) && $value = Helper::get_settings( $hash[ $prop ][0] ) ) { // phpcs:ignore
			$entity[ $hash[ $prop ][1] ] = $value;
			return;
		}

		$perform = "add_prop_{$prop}";
		if ( method_exists( $this, $perform ) ) {
			$this->$perform( $entity, $key, $data );
		}
	}

	/**
	 * Add Language property to the entity.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_prop_language( &$entity ) {
		$entity['inLanguage'] = $this->do_filter( 'schema/language', get_bloginfo( 'language' ) );
	}

	/**
	 * Add Image property to entity.
	 *
	 * @param  array  $entity Array of json-ld entity.
	 * @param  string $key   Entity Key.
	 * @param  array  $data  Schema Data.
	 */
	private function add_prop_thumbnail( &$entity, $key, $data ) {
		if ( ! empty( $data['primaryImage'] ) ) {
			$entity[ $key ] = [ '@id' => $data['primaryImage']['@id'] ];
		}
	}

	/**
	 * Add URL property to entity.
	 *
	 * @param array  $entity Array of json-ld entity.
	 * @param string $key    Entity Key.
	 */
	private function add_prop_is_part_of( &$entity, $key ) {
		$hash = [
			'website' => home_url( '/#website' ),
			'webpage' => Paper::get()->get_canonical() . '#webpage',
		];

		if ( ! empty( $hash[ $key ] ) ) {
			$entity['isPartOf'] = [ '@id' => $hash[ $key ] ];
		}
	}

	/**
	 * Add Publisher property to entity
	 *
	 * @param array  $entity Entity.
	 * @param string $key    Entity Key.
	 * @param  array  $data  Schema Data.
	 */
	public function add_prop_publisher( &$entity, $key, $data ) {
		if ( ! isset( $data['publisher'] ) ) {
			return;
		}

		$entity[ $key ] = [ '@id' => $data['publisher']['@id'] ];
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_prop_url( &$entity ) {
		if ( $url = Helper::get_settings( 'titles.url' ) ) { // phpcs:ignore
			$entity['url'] = ! Url::is_relative( $url ) ? $url : 'http://' . $url;
		}
	}

	/**
	 * Add property to entity.
	 *
	 * @param array $entity Array of JSON-LD entity.
	 */
	private function add_prop_address( &$entity ) {
		if ( $address = Helper::get_settings( 'titles.local_address' ) ) { // phpcs:ignore
			$entity['address'] = [ '@type' => 'PostalAddress' ] + $address;
		}
	}

	/**
	 * Add aggregateratings to entity.
	 *
	 * @param string $schema Schema to get data for.
	 * @param array  $entity Array of JSON-LD entity to attach data to.
	 */
	public function add_ratings( $schema, &$entity ) {
		$rating = Helper::get_post_meta( "snippet_{$schema}_rating" );

		// Early Bail!
		if ( ! $rating ) {
			return;
		}

		$entity['review'] = [
			'author'        => [
				'@type' => 'Person',
				'name'  => get_the_author_meta( 'display_name' ),
			],
			'datePublished' => get_post_time( 'Y-m-d\TH:i:sP', false ),
			'dateModified'  => get_post_modified_time( 'Y-m-d\TH:i:sP', false ),
			'reviewRating'  => [
				'@type'       => 'Rating',
				'ratingValue' => $rating,
				'bestRating'  => Helper::get_post_meta( "snippet_{$schema}_rating_max" ) ? Helper::get_post_meta( "snippet_{$schema}_rating_max" ) : 5,
				'worstRating' => Helper::get_post_meta( "snippet_{$schema}_rating_min" ) ? Helper::get_post_meta( "snippet_{$schema}_rating_min" ) : 1,
			],
		];
	}

	/**
	 * Get website name with a fallback to bloginfo( 'name' ).
	 *
	 * @return string
	 */
	public function get_website_name() {
		$name = Helper::get_settings( 'titles.knowledgegraph_name' );

		return $name ? $name : get_bloginfo( 'name' );
	}

	/**
	 * Set publisher/provider data for JSON-LD.
	 *
	 * @param array  $entity Array of JSON-LD entity.
	 * @param array  $organization Organization data.
	 * @param string $type         Type data set to. Default: 'publisher'.
	 */
	public function set_publisher( &$entity, $organization, $type = 'publisher' ) {
		$keys = [ '@context', '@type', 'url', 'name', 'logo', 'image', 'contactPoint', 'sameAs' ];
		foreach ( $keys as $key ) {
			if ( ! isset( $organization[ $key ] ) ) {
				continue;
			}

			$entity[ $type ][ $key ] = 'logo' !== $key ? $organization[ $key ] : [
				'@type' => 'ImageObject',
				'url'   => $organization[ $key ],
			];
		}
	}

	/**
	 * Set address for JSON-LD.
	 *
	 * @param string $schema Schema to get data for.
	 * @param array  $entity Array of JSON-LD entity to attach data to.
	 */
	public function set_address( $schema, &$entity ) {
		$address = Helper::get_post_meta( "snippet_{$schema}_address" );

		// Early Bail!
		if ( ! is_array( $address ) || empty( $address ) ) {
			return;
		}

		$entity['address'] = [ '@type' => 'PostalAddress' ];
		foreach ( $address as $key => $value ) {
			$entity['address'][ $key ] = $value;
		}
	}

	/**
	 * Set data to entity.
	 *
	 * Loop through post meta value grab data and attache it to the entity.
	 *
	 * @param array $hash   Key to get data and Value to save as.
	 * @param array $entity Array of JSON-LD entity to attach data to.
	 */
	public function set_data( $hash, &$entity ) {
		foreach ( $hash as $metakey => $dest ) {
			$entity[ $dest ] = Helper::get_post_meta( $metakey, $this->post_id );
		}
	}

	/**
	 * Get post parts.
	 */
	private function get_parts() {
		$parts = [
			'title'     => $this->get_post_title(),
			'url'       => $this->get_post_url(),
			'canonical' => Paper::get()->get_canonical(),
			'modified'  => mysql2date( DATE_W3C, $this->post->post_modified, false ),
			'published' => mysql2date( DATE_W3C, $this->post->post_date, false ),
			'excerpt'   => Helper::replace_vars( '%excerpt%', $this->post ),
		];

		// Description.
		$desc = Helper::get_post_meta( 'snippet_desc' );

		if ( ! $desc ) {
			$desc = Helper::replace_vars( Helper::get_settings( "titles.pt_{$this->post->post_type}_default_snippet_desc" ), $this->post );
		}
		$parts['desc'] = $desc ? $desc : ( Helper::get_post_meta( 'description' ) ? Helper::get_post_meta( 'description' ) : $parts['excerpt'] );

		// Author.
		$author          = Helper::get_post_meta( 'snippet_author' );
		$parts['author'] = $author ? $author : get_the_author_meta( 'display_name', $this->post->post_author );

		$this->parts = $parts;
	}

	/**
	 * Get post title.
	 *
	 * Retrieves the title in this order.
	 *  1. Custom post meta set in rich snippet
	 *  2. Headline template set in Titles & Meta
	 *
	 * @param int $post_id Post ID to get title for.
	 *
	 * @return string
	 */
	public function get_post_title( $post_id = 0 ) {
		$title = Helper::get_post_meta( 'snippet_name', $post_id );

		if ( ! $title && ! empty( $this->post ) ) {
			$title = Helper::replace_vars( Helper::get_settings( "titles.pt_{$this->post->post_type}_default_snippet_name", '%seo_title%' ), $this->post );
		}

		$title = $title ? $title : Paper::get()->get_title();

		return Str::truncate( $title );
	}

	/**
	 * Get post url.
	 *
	 * @param  int $post_id Post ID to get URL for.
	 * @return string
	 */
	public function get_post_url( $post_id = 0 ) {
		$url = Helper::get_post_meta( 'snippet_url', $post_id );

		return $url ? $url : ( 0 === $post_id ? Paper::get()->get_canonical() : get_the_permalink( $post_id ) );
	}

	/**
	 * Get product description.
	 *
	 * @param  object $product Product Object.
	 * @return string
	 */
	public function get_product_desc( $product = [] ) {
		if ( empty( $product ) ) {
			return;
		}

		if ( $description = Helper::get_post_meta( 'description', $product->get_id() ) ) { //phpcs:ignore
			return $description;
		}

		$description = $product->get_short_description() ? $product->get_short_description() : $product->get_description();
		$description = $this->do_filter( 'product_description/apply_shortcode', false ) ? do_shortcode( $description ) : WordPress::strip_shortcodes( $description );
		return wp_strip_all_tags( $description, true );
	}

	/**
	 * Get product title.
	 *
	 * @param  object $product Product Object.
	 * @return string
	 */
	public function get_product_title( $product = [] ) {
		if ( empty( $product ) ) {
			return '';
		}

		if ( $title = Helper::get_post_meta( 'title', $product->get_id() ) ) { //phpcs:ignore
			return $title;
		}

		return $product->get_name();
	}
}
