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
use RankMath\Helpers\Str;

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
	 * Convert Schema Pro variables if needed.
	 *
	 * @param string $value Value to convert.
	 *
	 * @return string
	 */
	public function convert_variables( $value ) {
		$value = str_replace( 'blogname', '%sitename%', $value );
		$value = str_replace( 'blogdescription', '%sitedesc%', $value );
		$value = str_replace( 'site_url', get_bloginfo( 'url' ), $value );
		$value = str_replace( 'site_logo', get_theme_mod( 'custom_logo' ), $value );
		$value = str_replace( 'featured_image', '', $value );
		$value = str_replace( 'featured_img', '', $value );
		$value = str_replace( 'post_title', '%seo_title%', $value );
		$value = str_replace( 'post_excerpt', '%seo_description%', $value );
		$value = str_replace( 'post_content', '%seo_description%', $value );
		$value = str_replace( 'post_date', '%date%', $value );
		$value = str_replace( 'post_modified', '%modified%', $value );
		$value = str_replace( 'post_permalink', '', $value );
		$value = str_replace( 'author_name', '%name%', $value );
		$value = str_replace( 'author_first_name', '%name%', $value );
		$value = str_replace( 'author_last_name', '%name%', $value );
		$value = str_replace( 'author_image', '', $value );

		return $value;
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

		$details = $snippet['details'];
		$methods = [
			'work-example' => 'get_book_editions',
			'steps'        => 'get_howto_steps',
			'tool'         => 'get_howto_tools',
			'supply'       => 'get_howto_supplies',
			'rating'       => 'get_rating',
		];

		$data = [];
		foreach ( $hash[ $type ] as $snippet_key => $snippet_value ) {
			$method = isset( $methods[ $snippet_key ] ) ? $methods[ $snippet_key ] : 'get_schema_meta';
			$value  = $this->$method( $details, $snippet_key, $post_id, $snippet, $snippet_value );

			$this->validate_schema_data( $data, $value, $snippet_value, $snippet_key );
		}

		if ( ! empty( $data ) ) {
			if ( isset( $data['schema-type'] ) ) {
				$type = $data['schema-type'];
				unset( $data['schema-type'] );
			}

			$type             = $this->sanitize_schema_type( $type );
			$data['@type']    = $type;
			$data['metadata'] = [
				'title'     => Helper::sanitize_schema_title( $type ),
				'type'      => 'template',
				'isPrimary' => 1,
				'shortcode' => uniqid( 's-' ),
			];

			$type = in_array( $type, [ 'BlogPosting', 'NewsArticle' ], true ) ? 'Article' : $type;
			update_post_meta( $post_id, 'rank_math_schema_' . $type, $data );
		}
	}

	/**
	 * Validate schema data.
	 *
	 * @param array  $data        Schema entity data.
	 * @param string $value       Entity value.
	 * @param string $key         Entity key.
	 * @param string $snippet_key Snippet key.
	 */
	private function validate_schema_data( &$data, $value, $key, $snippet_key ) {
		if ( 'question-answer' === $snippet_key && ! empty( $value ) ) {
			foreach ( $value as $question ) {
				$data[ $key ][] = [
					'@type'          => 'Question',
					'name'           => $question['question'],
					'acceptedAnswer' => [
						'@type' => 'Answer',
						'text'  => $question['answer'],
					],
				];
			}

			return;
		}

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
		if ( 'location' === $key || 'jobLocation' === $key ) {
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

		if ( 'baseSalary' === $key ) {
			$data['@type'] = 'MonetaryAmount';
		}

		if ( 'value' === $key ) {
			$data['@type'] = 'QuantitativeValue';
		}

		if ( 'performer' === $key ) {
			$data['@type'] = 'Person';
		}

		if ( 'provider' === $key || 'hiringOrganization' === $key ) {
			$data['@type'] = 'Organization';
		}
	}

	/**
	 * Get ratings value.
	 *
	 * @param  array  $details       Array of details.
	 * @param  string $snippet_key   Snippet key.
	 * @param  string $post_id       Post ID.
	 * @param  array  $snippet       Snippet data.
	 * @param  string $snippet_value Snippet value.
	 * @return string
	 */
	private function get_rating( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		return get_post_meta( $post_id, 'bsf-schema-pro-rating-' . $snippet['id'], true );
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
	private function get_howto_steps( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		$steps = get_post_meta( $post_id, "how-to-{$snippet['id']}-steps", true );
		if ( empty( $steps ) ) {
			return [];
		}

		$data = [];
		foreach ( $steps as $step ) {
			$entity = [
				'@type' => 'HowToStep',
				'name'  => $step['name'],
				'url'   => $step['url'],
			];

			if ( ! empty( $step['description'] ) ) {
				$entity['itemListElement'] = [
					'@type' => 'HowToDirection',
					'text'  => $step['description'],
				];
			}

			if ( ! empty( $step['image'] ) ) {
				$entity['image'] = [
					'@type' => 'ImageObject',
					'text'  => wp_get_attachment_url( $step['image'] ),
				];
			}

			$data[] = $entity;
		}

		return $data;
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
	private function get_howto_tools( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		$tools = get_post_meta( $post_id, "how-to-{$snippet['id']}-tool", true );
		if ( empty( $tools ) ) {
			return [];
		}

		$data = [];
		foreach ( $tools as $tool ) {
			$data[] = [
				'@type' => 'HowToTool',
				'name'  => $tool['name'],
			];
		}

		return $data;
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
	private function get_howto_supplies( $details, $snippet_key, $post_id, $snippet, $snippet_value ) {
		$supplies = get_post_meta( $post_id, "how-to-{$snippet['id']}-supply", true );
		if ( empty( $supplies ) ) {
			return [];
		}

		$data = [];
		foreach ( $supplies as $supply ) {
			$data[] = [
				'@type' => 'HowToSupply',
				'name'  => $supply['name'],
			];
		}

		return $data;
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
		$key = isset( $data['details'][ $data[ $key . '-specific-field' ] ] ) ? $data['details'][ $data[ $key . '-specific-field' ] ] : '';
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
			'job-posting'          => 'JobPosting',
			'video-object'         => 'VideoObject',
			'software-application' => 'SoftwareApplication',
			'faq'                  => 'FAQPage',
			'how-to'               => 'HowTo',
		];

		$type = in_array( $type, [ 'AdvertiserContentArticle', 'Report', 'SatiricalArticle', 'ScholarlyArticle', 'TechArticle' ], true )
				? 'Article'
				: $type;

		return isset( $hash[ $type ] ) ? $hash[ $type ] : ucfirst( $type );
	}

	/**
	 * Get Snippet Details stored in aiosrs-schema posts
	 *
	 * @param int $post_id Post ID.
	 * @return array
	 */
	private function get_snippet_details( $post_id ) {
		global $wpdb;

		$post_type = addcslashes( get_post_type( $post_id ), '_' );
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

		return false;
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
				'name'        => 'headline',
				'description' => 'description',
				'schema-type' => 'schema-type',
			],
			'book'                 => [
				'name'         => 'name',
				'url'          => 'url',
				'author'       => 'author.name',
				'work-example' => 'book_editions',
				'rating'       => 'review.reviewRating.ratingValue',
			],
			'course'               => [
				'name'             => 'name',
				'description'      => 'description',
				'orgnization-name' => 'provider.name',
				'same-as'          => 'provider.sameAs',
				'rating'           => 'review.reviewRating.ratingValue',
			],
			'person'               => [
				'name'      => 'name',
				'email'     => 'email',
				'gender'    => 'gender',
				'job-title' => 'jobTitle',
				'street'    => 'address.streetAddress',
				'locality'  => 'address.addressLocality',
				'region'    => 'address.addressRegion',
				'postal'    => 'address.postalCode',
				'country'   => 'address.addressCountry',
			],
			'service'              => [
				'name'        => 'name',
				'description' => 'description',
				'type'        => 'serviceType',
				'price-range' => 'offers.price',
			],
			'faq'                  => [
				'question-answer' => 'mainEntity',
			],
			'how-to'               => [
				'name'        => 'name',
				'description' => 'description',
				'total-time'  => 'totalTime',
				'steps'       => 'step',
				'supply'      => 'supply',
				'tool'        => 'tool',
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
			'description'           => 'description',
			'schema-type'           => 'schema-type',
			'event-status'          => 'eventStatus',
			'event-attendance-mode' => 'eventAttendanceMode',
			'start-date'            => 'startDate',
			'end-date'              => 'endDate',
			'location'              => 'location.name',
			'location-street'       => 'location.address.streetAddress',
			'location-locality'     => 'location.address.addressLocality',
			'location-region'       => 'location.address.addressRegion',
			'location-postal'       => 'location.address.postalCode',
			'location-country'      => 'location.address.addressCountry',
			'ticket-buy-url'        => 'offers.url',
			'price'                 => 'offers.price',
			'currency'              => 'offers.priceCurrency',
			'avail'                 => 'offers.availability',
			'performer'             => 'performer.name',
			'rating'                => 'review.reviewRating.ratingValue',
		];
	}

	/**
	 * Get job_posting fields.
	 *
	 * @return array
	 */
	private function get_job_posting_fields() {
		return [
			'title'             => 'title',
			'description'       => 'description',
			'job-type'          => 'employmentType',
			'start-date'        => 'datePosted',
			'expiry-date'       => 'validThrough',
			'orgnization-name'  => 'hiringOrganization.name',
			'same-as'           => 'hiringOrganization.sameAs',
			'organization-logo' => 'hiringOrganization.logo',
			'location-street'   => 'jobLocation.address.streetAddress',
			'location-locality' => 'jobLocation.address.addressLocality',
			'location-region'   => 'jobLocation.address.addressRegion',
			'location-postal'   => 'jobLocation.address.postalCode',
			'location-country'  => 'jobLocation.address.addressCountry',
			'salary'            => 'baseSalary.value.value',
			'salary-currency'   => 'baseSalary.currency',
			'salary-unit'       => 'baseSalary.value.unitText',
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
			'description'       => 'description',
			'sku'               => 'sku',
			'brand-name'        => 'brand.name',
			'price'             => 'offers.price',
			'currency'          => 'offers.priceCurrency',
			'avail'             => 'offers.availability',
			'price-valid-until' => 'offers.priceValidUntil',
			'rating'            => 'review.reviewRating.ratingValue',
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
			'description'      => 'description',
			'recipe-category'  => 'recipeCategory',
			'recipe-cuisine'   => 'recipeCuisine',
			'recipe-yield'     => 'recipeYield',
			'recipe-keywords'  => 'keywords',
			'nutrition'        => 'nutrition.calories',
			'preperation-time' => 'prepTime',
			'cook-time'        => 'cookTime',
			'ingredients'      => 'recipeIngredient',
			'rating'           => 'review.reviewRating.ratingValue',
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
			'rating'           => 'review.reviewRating.ratingValue',
			'price'            => 'offers.price',
			'currency'         => 'offers.priceCurrency',
			'operating-system' => 'operatingSystem',
			'category'         => 'applicationCategory',
		];
	}

	/**
	 * Get video fields.
	 *
	 * @return array
	 */
	private function get_video_fields() {
		return [
			'name'        => 'name',
			'description' => 'description',
			'content-url' => 'contentUrl',
			'embed-url'   => 'embedUrl',
			'duration'    => 'duration',
			'rating'      => 'review.reviewRating.ratingValue',
		];
	}
}
