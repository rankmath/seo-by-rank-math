<?php
/**
 * The WP Schema Pro Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Schema\JsonLD;
use RankMath\Schema\Singular;

defined( 'ABSPATH' ) || exit;

/**
 * WP_Schema_Pro class.
 */
class WP_Schema_Pro extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'WP Schema Pro';

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key = 'bsf-aiosrs';

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys = [ 'wp-schema-pro-general-settings', 'wp-schema-pro-social-profiles', 'wp-schema-pro-global-schemas' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'settings', 'postmeta' ];

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
	 * Convert SEOPress variables if needed.
	 *
	 * @param string $string Value to convert.
	 *
	 * @return string
	 */
	public function convert_variables( $string ) {
		$string = str_replace( 'blogname', '%sitename%', $string );
		$string = str_replace( 'blogdescription', '%sitedesc%', $string );
		$string = str_replace( 'site_url', get_bloginfo( 'url' ), $string );
		$string = str_replace( 'site_logo', get_theme_mod( 'custom_logo' ), $string );
		$string = str_replace( 'featured_image', '', $string );
		$string = str_replace( 'featured_img', '', $string );
		$string = str_replace( 'post_title', '%seo_title%', $string );
		$string = str_replace( 'post_excerpt', '%seo_description%', $string );
		$string = str_replace( 'post_content', '%seo_description%', $string );
		$string = str_replace( 'post_date', '%date%', $string );
		$string = str_replace( 'post_modified', '%modified%', $string );
		$string = str_replace( 'post_permalink', '', $string );
		$string = str_replace( 'author_name', '%name%', $string );
		$string = str_replace( 'author_first_name', '%name%', $string );
		$string = str_replace( 'author_last_name', '%name%', $string );
		$string = str_replace( 'author_image', '', $string );

		return $string;
	}

	/**
	 * Import settings of plugin.
	 *
	 * @return bool
	 */
	protected function settings() {
		$this->get_settings();

		$schema_general = get_option( 'wp-schema-pro-general-settings' );
		$schema_social  = get_option( 'wp-schema-pro-social-profiles' );
		$schema_global  = get_option( 'wp-schema-pro-global-schemas' );

		// Knowledge Graph Logo.
		if ( isset( $schema_general['site-logo-custom'] ) ) {
			$this->replace_image( $schema_general['site-logo-custom'], $this->titles, 'knowledgegraph_logo', 'knowledgegraph_logo_id' );
		}

		// General.
		$hash = [ 'site-represent' => 'knowledgegraph_type' ];

		$has_key          = 'person' === $schema_general['site-represent'] ? 'person-name' : 'site-name';
		$hash[ $has_key ] = 'knowledgegraph_name';
		$this->replace( $hash, $schema_general, $this->titles );

		$this->titles['local_seo'] = isset( $schema_general['site-represent'] ) && ! empty( $yoast_titles['site-represent'] ) ? 'on' : 'off';

		// Social.
		$hash = [
			'facebook' => 'social_url_facebook',
			'twitter'  => 'twitter_author_names',
		];
		$this->replace( $hash, $schema_social, $this->titles );

		// About & Contact Page.
		$hash = [
			'about-page'   => 'local_seo_about_page',
			'contact-page' => 'local_seo_contact_page',
		];
		$this->replace( $hash, $schema_global, $this->titles );

		$this->update_settings();

		return true;
	}

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
			$post_id = $snippet_post->ID;
			$snippet = $this->get_snippet_details( $post_id );
			if ( ! $snippet ) {
				continue;
			}

			$this->update_postmeta( $post_id, $snippet );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Update post meta.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $snippet Snippet data.
	 */
	private function update_postmeta( $post_id, $snippet ) {
		$type = $snippet['type'];
		$hash = $this->get_schema_types();
		if ( ! isset( $hash[ $type ] ) ) {
			return;
		}

		$schema_type = $this->sanitize_schema_type( $type );
		$details     = $snippet['details'];
		$methods     = [
			'work-example' => 'get_book_editions',
			'address'      => 'get_address',
		];

		foreach ( $hash[ $type ] as $snippet_key => $snippet_value ) {

			if ( 'address' === $snippet_key ) {
				$value = $this->get_address( $details, $snippet_key, $post_id, $snippet, $snippet_value );
				update_post_meta( $post_id, 'rank_math_snippet_' . $schema_type . '_address', $value );
				continue;
			}

			$method = isset( $methods[ $snippet_key ] ) ? $methods[ $snippet_key ] : 'get_schema_meta';
			$value  = $this->$method( $details, $snippet_key, $post_id, $snippet, $snippet_value );
			update_post_meta( $post_id, 'rank_math_snippet_' . $snippet_value, $value );
		}

		update_post_meta( $post_id, 'rank_math_rich_snippet', $schema_type );

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
	 * Get address
	 *
	 * @param  array  $details       Array of details.
	 * @param  string $snippet_key   Snippet key.
	 * @param  string $post_id       Post ID.
	 * @param  array  $snippet       Snippet data.
	 * @param  string $snippet_value Snippet value.
	 * @return string
	 */
	private function get_address( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		if ( empty( $snippet_value ) ) {
			return '';
		}

		$address = [];
		foreach ( $snippet_value as $key => $meta ) {
			$address[ $meta ] = $this->get_schema_meta( $details, $key, $post_id, $snippet, $snippet_value );
		}

		return $address;
	}

	/**
	 * Get post meta for schema plugin
	 *
	 * @param  array  $details       Array of details.
	 * @param  string $snippet_key   Snippet key.
	 * @param  string $post_id       Post ID.
	 * @param  array  $snippet       Snippet data.
	 * @param  string $snippet_value Snippet value.
	 * @return string
	 */
	private function get_schema_meta( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		$value = isset( $details[ $snippet_key ] ) ? $details[ $snippet_key ] : '';
		if ( 'custom-text' === $value ) {
			$value = isset( $details[ $snippet_key . '-custom-text' ] ) ? $details[ $snippet_key . '-custom-text' ] : '';
		}

		if ( 'create-field' === $value ) {
			$value = get_post_meta( $post_id, $snippet['type'] . '-' . $snippet['id'] . '-' . $snippet_key, true );
		}

		if ( 'specific-field' === $value ) {
			$key   = isset( $details[ $snippet_key . '-specific-field' ] ) ? $details[ $snippet_key . '-specific-field' ] : '';
			$value = get_post_meta( $post_id, $key, true );
		}

		return $this->convert_variables( $value );
	}

	/**
	 * Get Book Editions.
	 *
	 * @param  array  $details       Array of details.
	 * @param  string $snippet_key   Snippet key.
	 * @param  string $post_id       Post ID.
	 * @param  array  $snippet       Snippet data.
	 * @param  string $snippet_value Snippet value.
	 * @return string
	 */
	private function get_book_editions( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		if ( empty( $details[ $snippet_key ] ) ) {
			return '';
		}

		$editions = [];
		$data     = [
			'details'       => $details,
			'snippet_key'   => $snippet_key,
			'post_id'       => $post_id,
			'snippet'       => $snippet,
			'snippet_value' => $snippet_value,
		];
		foreach ( $details[ $snippet_key ] as $key => $edition ) {
			$editions[] = [
				'book_edition' => $this->normalize_edition( $key . '-book-edition', $edition['book-edition'], $data ),
				'isbn'         => $this->normalize_edition( $key . '-serial-number', $edition['serial-number'], $data ),
				'url'          => $this->normalize_edition( $key . '-url-template', $edition['url-template'], $data ),
				'book_format'  => $this->normalize_edition( $key . '-book-format', $edition['book-format'], $data ),
			];
		}

		return $editions;
	}

	/**
	 * Normalize Book Edition.
	 *
	 * @param  string $key   Custom field key.
	 * @param  string $value Custom field value.
	 * @param  array  $data  Snippet data.
	 * @return string
	 */
	private function normalize_edition( $key, $value, $data ) {
		if ( ! $value ) {
			return '';
		}

		$hash = [
			'custom-text'    => 'get_custom_text',
			'create-field'   => 'get_created_field',
			'specific-field' => 'get_specific_field',
		];
		if ( isset( $hash[ $value ] ) ) {
			$method = $hash[ $value ];
			$value  = $this->$method( $key, $value, $data );
		}

		return $this->convert_variables( $value );
	}

	/**
	 * Get Custom Text added in the Settings.
	 *
	 * @param  string $key   Custom field key.
	 * @param  string $value Custom field value.
	 * @param  array  $data  Snippet data.
	 * @return string
	 */
	private function get_custom_text( $key, $value, $data ) {
		$key = $data['snippet_key'] . '-custom-text';
		return isset( $data['details'][ $key ] ) ? $data['details'][ $key ] : '';
	}

	/**
	 * Get Created field value added in the post metabox.
	 *
	 * @param  string $key   Custom field key.
	 * @param  string $value Custom field value.
	 * @param  array  $data  Snippet data.
	 * @return string
	 */
	private function get_created_field( $key, $value, $data ) {
		$meta_key = $data['snippet']['type'] . '-' . $data['snippet']['id'] . '-' . $data['snippet_key'] . '-' . $key;
		return get_post_meta( $data['post_id'], $meta_key, true );
	}

	/**
	 * Get Specific Custom field value.
	 *
	 * @param  string $key   Custom field key.
	 * @param  string $value Custom field value.
	 * @param  array  $data  Snippet data.
	 * @return string
	 */
	private function get_specific_field( $key, $value, $data ) {
		$key = isset( $data['details'][ $data[ $snippet_key . '-specific-field' ] ] ) ? $data['details'][ $data[ $snippet_key . '-specific-field' ] ] : '';
		return get_post_meta( $data['post_id'], $key, true );
	}

	/**
	 * Sanitize schema type before saving
	 *
	 * @param  string $type Schema type to sanitize.
	 * @return string
	 */
	private function sanitize_schema_type( $type ) {
		$hash = [
			'job-posting'          => 'jobposting',
			'video-object'         => 'video',
			'software-application' => 'software',
		];

		return isset( $hash[ $type ] ) ? $hash[ $type ] : $type;
	}

	/**
	 * Get Snippet Details stored in aiosrs-schema posts
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_snippet_details( $post_id ) {
		global $wpdb;

		$post_type = get_post_type( $post_id );
		$query     = "SELECT p.ID, pm.meta_value FROM {$wpdb->postmeta} as pm
		INNER JOIN {$wpdb->posts} as p ON pm.post_id = p.ID
		WHERE pm.meta_key = 'bsf-aiosrs-schema-location'
		AND p.post_type = 'aiosrs-schema'
		AND p.post_status = 'publish'";

		$orderby    = ' ORDER BY p.post_date DESC LIMIT 1';
		$meta_args  = "pm.meta_value LIKE '%\"basic-global\"%'";
		$meta_args .= " OR pm.meta_value LIKE '%\"basic-singulars\"%'";
		$meta_args .= " OR pm.meta_value LIKE '%\"{$post_type}|all\"%'";
		$meta_args .= " OR pm.meta_value LIKE '%\"post-{$post_id}\"%'";

		$local_posts = $wpdb->get_col( $query . ' AND (' . $meta_args . ')' . $orderby ); // phpcs:ignore
		if ( empty( $local_posts ) ) {
			return false;
		}

		$current_page_data = [];
		foreach ( $local_posts as $local_post ) {
			$snippet_type = get_post_meta( $local_post, 'bsf-aiosrs-schema-type', true );

			return [
				'id'      => $local_post,
				'type'    => $snippet_type,
				'details' => get_post_meta( $local_post, 'bsf-aiosrs-' . $snippet_type, true ),
			];
		}
	}

	/**
	 * Get the actions which can be performed for the plugin.
	 *
	 * @return array
	 */
	public function get_choices() {
		return [
			'settings' => esc_html__( 'Import Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Plugin settings and site-wide meta data.', 'rank-math' ) ),
			'postmeta' => esc_html__( 'Import Schemas', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import all Schema data for Posts, Pages, and custom post types.', 'rank-math' ) ),
		];
	}

	/**
	 * Get schema types
	 *
	 * @return array
	 */
	private function get_schema_types() {
		return [
			'event'                => $this->get_event_fields(),
			'job-posting'          => $this->get_job_posting_fields(),
			'product'              => $this->get_product_fields(),
			'recipe'               => $this->get_recipe_fields(),
			'software-application' => $this->get_software_fields(),
			'video-object'         => $this->get_video_fields(),
			'article'              => [
				'name'        => 'name',
				'description' => 'desc',
				'schema-type' => 'article_type',
			],
			'book'                 => [
				'name'         => 'name',
				'url'          => 'url',
				'author'       => 'author',
				'work-example' => 'book_editions',
			],
			'course'               => [
				'name'             => 'name',
				'description'      => 'desc',
				'orgnization-name' => 'provider',
				'same-as'          => 'course_provider_url',
				'rating'           => 'course_rating',
			],
			'review'               => [
				'item'        => 'name',
				'description' => 'desc',
				'rating'      => 'review_rating_value',
			],
			'person'               => [
				'name'      => 'name',
				'email'     => 'person_email',
				'gender'    => 'person_gender',
				'job-title' => 'person_job_title',
				'address'   => [
					'street'   => 'streetAddress',
					'locality' => 'addressLocality',
					'region'   => 'addressRegion',
					'postal'   => 'postalCode',
					'country'  => 'addressCountry',
				],
			],
			'service'              => [
				'name'        => 'name',
				'description' => 'desc',
				'type'        => 'service_type',
				'price-range' => 'price',
			],
		];
	}

	/**
	 * Get event fields.
	 *
	 * @return array
	 */
	private function get_event_fields() {
		return [
			'name'                  => 'name',
			'description'           => 'desc',
			'schema-type'           => 'event_type',
			'event-status'          => 'event_status',
			'event-attendance-mode' => 'event_attendance_mode',
			'ticket-buy-url'        => 'event_ticketurl',
			'location'              => 'event_venue',
			'start-date'            => 'event_startdate',
			'end-date'              => 'event_enddate',
			'price'                 => 'event_price',
			'currency'              => 'event_currency',
			'avail'                 => 'event_availability',
			'performer'             => 'event_performer',
		];
	}

	/**
	 * Get job_posting fields.
	 *
	 * @return array
	 */
	private function get_job_posting_fields() {
		return [
			'title'             => 'name',
			'description'       => 'desc',
			'salary'            => 'jobposting_salary',
			'salary-currency'   => 'jobposting_currency',
			'salary-unit'       => 'jobposting_payroll',
			'job-type'          => 'jobposting_employment_type',
			'orgnization-name'  => 'jobposting_organization',
			'same-as'           => 'jobposting_url',
			'organization-logo' => 'jobposting_logo',
			'start-date'        => 'jobposting_startdate',
			'expiry-date'       => 'jobposting_expirydate',
			'address'           => [
				'location-street'   => 'streetAddress',
				'location-locality' => 'addressLocality',
				'location-region'   => 'addressRegion',
				'location-postal'   => 'postalCode',
				'location-country'  => 'addressCountry',
			],
		];
	}

	/**
	 * Get product fields.
	 *
	 * @return array
	 */
	private function get_product_fields() {
		return [
			'name'              => 'name',
			'description'       => 'desc',
			'brand-name'        => 'product_brand',
			'price'             => 'product_price',
			'currency'          => 'product_currency',
			'avail'             => 'product_instock',
			'sku'               => 'product_sku',
			'price-valid-until' => 'price_valid',
		];
	}

	/**
	 * Get recipe fields.
	 *
	 * @return array
	 */
	private function get_recipe_fields() {
		return [
			'name'             => 'name',
			'description'      => 'desc',
			'recipe-category'  => 'recipe_type',
			'recipe-cuisine'   => 'recipe_cuisine',
			'recipe-keywords'  => 'recipe_keywords',
			'nutrition'        => 'recipe_calories',
			'preperation-time' => 'recipe_preptime',
			'cook-time'        => 'recipe_cooktime',
			'ingredients'      => 'recipe_ingredients',
		];
	}

	/**
	 * Get software fields.
	 *
	 * @return array
	 */
	private function get_software_fields() {
		return [
			'name'             => 'name',
			'rating'           => 'software_rating_value',
			'review-count'     => 'software_rating_count',
			'price'            => 'software_price',
			'currency'         => 'software_price_currency',
			'operating-system' => 'software_operating_system',
			'category'         => 'software_application_category',
		];
	}

	/**
	 * Get video fields.
	 *
	 * @return array
	 */
	private function get_video_fields() {
		return [
			'name'              => 'name',
			'description'       => 'desc',
			'content-url'       => 'video_url',
			'embed-url'         => 'video_embed_url',
			'duration'          => 'video_duration',
			'interaction-count' => 'video_views',
		];
	}
}
