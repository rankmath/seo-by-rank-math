<?php
/**
 * The Yoast SEO Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Helper;
use RankMath\Redirections\Redirection;
use RankMath\Tools\Yoast_Blocks;
use RankMath\Helpers\DB;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Yoast class.
 */
class Yoast extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'Yoast SEO';

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key = '_yoast_wpseo_';

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys = [ 'wpseo', 'wpseo_%' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'settings', 'locations', 'news', 'video', 'postmeta', 'termmeta', 'usermeta', 'redirections', 'blocks' ];

	/**
	 * Table names to drop while cleaning.
	 *
	 * @var array
	 */
	protected $table_names = [ 'yoast_seo_links', 'yoast_seo_meta' ];

	/**
	 * Convert Yoast / AIO SEO variables if needed.
	 *
	 * @param string $string Value to convert.
	 *
	 * @return string
	 */
	public function convert_variables( $string ) {
		$string = str_replace( '%%term_title%%', '%term%', $string );
		$string = str_replace( '%%category_description%%', '%term_description%', $string );
		$string = str_replace( '%%searchphrase%%', '%search_query%', $string );
		$string = preg_replace( '/%%cf_([^%]+)%%/i', '%customfield($1)%', $string );
		$string = preg_replace( '/%%ct_([^%]+)%%/i', '%customterm($1)%', $string );
		$string = preg_replace( '/%%ct_desc_([^%]+)%%/i', '%customterm($1)%', $string );

		return str_replace( '%%', '%', $string );
	}

	/**
	 * Import settings of plugin.
	 *
	 * @return bool
	 */
	protected function settings() {
		$this->get_settings();

		$yoast_main          = get_option( 'wpseo' );
		$yoast_social        = get_option( 'wpseo_social' );
		$yoast_titles        = get_option( 'wpseo_titles' );
		$yoast_internallinks = get_option( 'wpseo_internallinks' );
		$yoast_sitemap       = get_option( 'wpseo_xml' );

		// Features.
		$modules  = [];
		$features = [
			'keyword_analysis_active' => 'seo-analysis',
			'enable_xml_sitemap'      => 'sitemap',
		];
		foreach ( $features as $feature => $module ) {
			$modules[ $module ] = 1 === intval( $yoast_main[ $feature ] ) ? 'on' : 'off';
		}
		Helper::update_modules( $modules );

		$this->titles['local_seo'] = isset( $yoast_titles['company_or_person'] ) && ! empty( $yoast_titles['company_or_person'] ) ? 'on' : 'off';

		// Titles & Descriptions.
		$hash = [
			'title-home-wpseo'       => 'homepage_title',
			'metadesc-home-wpseo'    => 'homepage_description',
			'title-author-wpseo'     => 'author_archive_title',
			'metadesc-author-wpseo'  => 'author_archive_description',
			'title-archive-wpseo'    => 'date_archive_title',
			'metadesc-archive-wpseo' => 'date_archive_description',
			'title-search-wpseo'     => 'search_title',
			'title-404-wpseo'        => '404_title',
			'org-description'        => 'organization_description',
		];
		$this->replace( $hash, $yoast_titles, $this->titles, 'convert_variables' );

		$this->local_seo_settings();
		$this->set_additional_organization_details( $yoast_titles );
		$this->set_separator( $yoast_titles );
		$this->set_post_types( $yoast_titles );
		$this->set_taxonomies( $yoast_titles );
		$this->sitemap_settings( $yoast_main, $yoast_sitemap );
		$this->social_webmaster_settings( $yoast_main, $yoast_social );
		$this->breadcrumb_settings( $yoast_titles, $yoast_internallinks );
		$this->misc_settings( $yoast_titles, $yoast_social );
		$this->slack_settings( $yoast_main );
		$this->update_settings();

		return true;
	}

	/**
	 * Set post type settings.
	 *
	 * @param array $yoast_titles Settings.
	 */
	private function set_post_types( $yoast_titles ) {
		$hash = [];
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$this->set_robots( "pt_{$post_type}", $post_type, $yoast_titles );

			$hash[ "title-{$post_type}" ]              = "pt_{$post_type}_title";
			$hash[ "metadesc-{$post_type}" ]           = "pt_{$post_type}_description";
			$hash[ "post_types-{$post_type}-maintax" ] = "pt_{$post_type}_primary_taxonomy";

			// Has Archive.
			$hash[ "title-ptarchive-{$post_type}" ]    = "pt_{$post_type}_archive_title";
			$hash[ "metadesc-ptarchive-{$post_type}" ] = "pt_{$post_type}_archive_description";

			// NOINDEX and Sitemap.
			$this->sitemap[ "pt_{$post_type}_sitemap" ] = isset( $yoast_titles[ "noindex-{$post_type}" ] ) && $yoast_titles[ "noindex-{$post_type}" ] ? 'off' : 'on';

			// Show/Hide Metabox.
			if ( isset( $yoast_titles[ "display-metabox-pt-{$post_type}" ] ) ) {
				$show = $yoast_titles[ "display-metabox-pt-{$post_type}" ]; // phpcs:ignore
				$this->titles[ "pt_{$post_type}_add_meta_box" ] = ( ! $show || 'off' === $show ) ? 'off' : 'on';
			}
		}

		$this->replace( $hash, $yoast_titles, $this->titles, 'convert_variables' );
	}

	/**
	 * Set taxonomies settings.
	 *
	 * @param array $yoast_titles Settings.
	 */
	private function set_taxonomies( $yoast_titles ) {
		$hash = [];
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$this->set_robots( "tax_{$taxonomy}", "tax-{$taxonomy}", $yoast_titles );

			$hash[ "title-tax-{$taxonomy}" ]    = "tax_{$taxonomy}_title";
			$hash[ "metadesc-tax-{$taxonomy}" ] = "tax_{$taxonomy}_description";

			// Show/Hide Metabox.
			$this->titles[ "tax_{$taxonomy}_add_meta_box" ] = isset( $yoast_titles[ "display-metabox-tax-{$taxonomy}" ] ) && $yoast_titles[ "display-metabox-tax-{$taxonomy}" ] ? 'on' : 'off';

			// Sitemap.
			$key   = "taxonomies-{$taxonomy}-not_in_sitemap";
			$value = isset( $yoast_sitemap[ $key ] ) ? ! $yoast_sitemap[ $key ] : false;

			$this->sitemap[ "tax_{$taxonomy}_sitemap" ] = $value ? 'on' : 'off';
		}
		$this->replace( $hash, $yoast_titles, $this->titles, 'convert_variables' );
	}

	/**
	 * Set robots settings.
	 *
	 * @param string $prefix       Setting prefix.
	 * @param string $yoast_prefix Setting prefix.
	 * @param array  $yoast_titles Settings.
	 */
	private function set_robots( $prefix, $yoast_prefix, $yoast_titles ) {
		if ( isset( $yoast_titles[ "noindex-{$yoast_prefix}" ] ) ) {
			$this->titles[ "{$prefix}_custom_robots" ] = 'on';
			$this->titles[ "{$prefix}_robots" ]        = [];
			if ( $yoast_titles[ "noindex-{$yoast_prefix}" ] ) {
				$this->titles[ "{$prefix}_robots" ][] = 'noindex';
				$this->titles[ "{$prefix}_robots" ]   = array_unique( $this->titles[ "{$prefix}_robots" ] );
			}
		}

		$this->titles[ "{$prefix}_add_meta_box" ] = isset( $yoast_titles[ "hideeditbox-{$yoast_prefix}" ] ) && $yoast_titles[ "hideeditbox-{$yoast_prefix}" ] ? 'off' : 'on';
	}

	/**
	 * Import post meta of plugin.
	 *
	 * @return array
	 */
	protected function postmeta() {
		$this->set_pagination( $this->get_post_ids( true ) );

		$post_ids = $this->get_post_ids();

		$this->set_primary_term( $post_ids );

		$hash = [
			'_yoast_wpseo_title'                 => 'rank_math_title',
			'_yoast_wpseo_metadesc'              => 'rank_math_description',
			'_yoast_wpseo_focuskw'               => 'rank_math_focus_keyword',
			'_yoast_wpseo_canonical'             => 'rank_math_canonical_url',
			'_yoast_wpseo_opengraph-title'       => 'rank_math_facebook_title',
			'_yoast_wpseo_opengraph-description' => 'rank_math_facebook_description',
			'_yoast_wpseo_twitter-title'         => 'rank_math_twitter_title',
			'_yoast_wpseo_twitter-description'   => 'rank_math_twitter_description',
			'_yoast_wpseo_bctitle'               => 'rank_math_breadcrumb_title',
			'_yoast_wpseo_newssitemap-exclude'   => 'rank_math_news_sitemap_exclude',
		];

		foreach ( $post_ids as $post ) {
			$post_id = $post->ID;
			$this->replace_meta( $hash, null, $post_id, 'post', 'convert_variables' );
			delete_post_meta( $post_id, 'rank_math_permalink' );

			// Cornerstone Content.
			$cornerstone = get_post_meta( $post_id, '_yoast_wpseo_is_cornerstone', true );
			if ( ! empty( $cornerstone ) ) {
				update_post_meta( $post_id, 'rank_math_pillar_content', 'on' );
			}

			$news_robots = get_post_meta( $post_id, '_yoast_wpseo_newssitemap-robots-index', true );
			$news_robots = ! empty( $news_robots ) ? 'noindex' : 'index';
			update_post_meta( $post_id, 'rank_math_news_sitemap_robots', $news_robots );

			$this->set_post_robots( $post_id );
			$this->replace_image( get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true ), 'post', 'rank_math_facebook_image', 'rank_math_facebook_image_id', $post_id );
			$this->replace_image( get_post_meta( $post_id, '_yoast_wpseo_twitter-image', true ), 'post', 'rank_math_twitter_image', 'rank_math_twitter_image_id', $post_id );
			$this->set_post_focus_keyword( $post_id );
			$this->is_twitter_using_facebook( 'post', $post_id );
			$this->add_schema_data( $post_id );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import Locations data from Yoast Local plugin.
	 *
	 * @return array
	 */
	protected function locations() {
		$this->import_locations_terms();
		$this->set_pagination( $this->get_location_ids( true ) );
		$locations = $this->get_location_ids();

		foreach ( $locations as $location ) {
			$args = (array) $location;
			unset( $args['ID'] );
			$args['post_type'] = 'rank_math_locations';

			$post_id = wp_insert_post( $args );
			if ( is_wp_error( $post_id ) ) {
				continue;
			}

			$post_terms = wp_get_object_terms( $location->ID, 'wpseo_locations_category', [ 'fields' => 'slugs' ] );
			wp_set_object_terms( $post_id, $post_terms, 'rank_math_location_category', false );

			$this->locations_meta( $location->ID, $post_id );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import Schema Data.
	 *
	 * @param int $post_id Post ID.
	 */
	private function add_schema_data( $post_id ) {
		$type = get_post_meta( $post_id, '_yoast_wpseo_schema_article_type', true );
		if ( empty( $type ) || ! in_array( $type, [ 'Article', 'BlogPosting', 'NewsArticle' ], true ) ) {
			return;
		}

		$data['@type']    = $type;
		$data['metadata'] = [
			'title'     => Helper::sanitize_schema_title( $type ),
			'type'      => 'template',
			'isPrimary' => 1,
			'shortcode' => uniqid( 's-' ),
		];

		update_post_meta( $post_id, 'rank_math_schema_' . $type, $data );
	}

	/**
	 * Import Locations terms.
	 */
	private function import_locations_terms() {
		$terms = get_terms( 'wpseo_locations_category' );
		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return;
		}

		foreach ( $terms as $term ) {
			wp_insert_term( $term->name, 'rank_math_location_category', $term );
		}
	}

	/**
	 * Import Locations metadata.
	 *
	 * @param int $old_post_id Yoast's location id.
	 * @param int $new_post_id Newly created location id.
	 */
	private function locations_meta( $old_post_id, $new_post_id ) {
		$metas = DB::query_builder( 'postmeta' )->where( 'post_id', $old_post_id )->select()->get();
		if ( empty( $metas ) ) {
			return;
		}

		$hash = [
			'_wpseo_business_type'                => '@type',
			'_wpseo_business_email'               => 'email',
			'_wpseo_business_url'                 => 'url',
			'_wpseo_business_address'             => 'address',
			'_wpseo_business_address_2'           => 'address',
			'_wpseo_business_city'                => 'address',
			'_wpseo_business_state'               => 'address',
			'_wpseo_business_zipcode'             => 'address',
			'_wpseo_business_country'             => 'address',
			'_wpseo_business_phone'               => 'telephone',
			'_wpseo_business_fax'                 => 'faxNumber',
			'_wpseo_business_location_logo'       => 'image',
			'_wpseo_business_vat_id'              => 'vatID',
			'_wpseo_business_tax_id'              => 'taxID',
			'_wpseo_business_price_range'         => 'priceRange',
			'_wpseo_business_currencies_accepted' => 'currenciesAccepted',
			'_wpseo_business_payment_accepted'    => 'paymentAccepted',
			'_wpseo_business_area_served'         => 'areaServed',
			'_wpseo_coordinates_lat'              => 'latitude',
			'_wpseo_coordinates_long'             => 'longitude',
			'_wpseo_business_phone_2nd'           => 'secondary_number',
			'_wpseo_business_coc_id'              => 'coc_id',
		];

		$schema        = [
			'name'     => '%seo_title%',
			'metadata' => [
				'type'  => 'template',
				'title' => 'LocalBusiness',
			],
			'geo'      => [
				'@type' => 'GeoCoordinates',
			],
		];
		$address       = [];
		$opening_hours = [];

		foreach ( $metas as $meta ) {
			if ( ! Str::starts_with( '_wpseo_', $meta->meta_key ) ) {
				update_post_meta( $new_post_id, $meta->meta_key, $meta->meta_value );
				continue;
			}

			if ( Str::starts_with( '_wpseo_opening_hours_', $meta->meta_key ) ) {
				$opening_hours[ $meta->meta_key ] = $meta->meta_value;
				continue;
			}

			if ( ! isset( $hash[ $meta->meta_key ] ) ) {
				continue;
			}

			if ( in_array( $hash[ $meta->meta_key ], [ 'secondary_number', 'coc_id' ], true ) ) {
				$schema['metadata'][ $hash[ $meta->meta_key ] ] = $meta->meta_value;
				continue;
			}

			if ( 'address' === $hash[ $meta->meta_key ] ) {
				$address[ $meta->meta_key ] = $meta->meta_value;
				continue;
			}

			if ( in_array( $hash[ $meta->meta_key ], [ 'latitude', 'longitude' ], true ) ) {
				$schema['geo'][ $hash[ $meta->meta_key ] ] = $meta->meta_value;
				continue;
			}

			$schema[ $hash[ $meta->meta_key ] ] = $meta->meta_value;
		}

		if ( ! empty( $address ) ) {
			$schema['address'] = $this->replace_address( $address );
		}

		if ( ! empty( $opening_hours ) ) {
			$schema['openingHoursSpecification'] = $this->replace_opening_hours( $opening_hours );
		}

		$schema['@type'] = 'LocalBusiness';

		if ( ! empty( $schema['image'] ) ) {
			$schema['image'] = [
				'@type' => 'ImageObject',
				'url'   => $schema['image'],
			];
		}

		if ( isset( $schema['geo']['latitude'] ) && isset( $schema['geo']['longitude'] ) ) {
			update_post_meta( $new_post_id, 'rank_math_local_business_latitide', $schema['geo']['latitude'] );
			update_post_meta( $new_post_id, 'rank_math_local_business_longitude', $schema['geo']['longitude'] );
		}

		update_post_meta( $new_post_id, 'rank_math_schema_' . $schema['@type'], $schema );
	}

	/**
	 * Replace Opening Hours data.
	 *
	 * @param array $opening_hours Opening Hours data.
	 * @return array Processed data.
	 */
	private function replace_opening_hours( $opening_hours ) {
		$data = [];
		$days = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
		foreach ( $days as $day ) {
			$opens  = ! empty( $opening_hours[ '_wpseo_opening_hours_' . strtolower( $day ) . '_from' ] ) ? $opening_hours[ '_wpseo_opening_hours_' . strtolower( $day ) . '_from' ] : 'closed';
			$closes = ! empty( $opening_hours[ '_wpseo_opening_hours_' . strtolower( $day ) . '_to' ] ) ? $opening_hours[ '_wpseo_opening_hours_' . strtolower( $day ) . '_to' ] : 'closed';

			if ( 'closed' === $opens ) {
				continue;
			}

			$data[ $day ] = [
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => $day,
				'opens'     => $opens,
				'closes'    => $closes,
			];
		}

		return array_values( $data );
	}

	/**
	 * Replace Address data.
	 *
	 * @param array $address Address data.
	 * @return array Processed data.
	 */
	private function replace_address( $address ) {
		$data = [
			'@type' => 'PostalAddress',
		];
		$hash = [
			'_wpseo_business_address'   => 'streetAddress',
			'_wpseo_business_address_2' => 'addressLocality',
			'_wpseo_business_state'     => 'addressRegion',
			'_wpseo_business_zipcode'   => 'postalCode',
			'_wpseo_business_country'   => 'addressCountry',
		];

		foreach ( $hash as $key => $value ) {
			$data[ $value ] = isset( $address[ $key ] ) ? $address[ $key ] : '';
		}

		if ( ! empty( $address['_wpseo_business_city'] ) ) {
			$data['addressLocality'] = $data['addressLocality'] . ', ' . $address['_wpseo_business_city'];
		}

		return $data;
	}

	/**
	 * Get all location IDs.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	private function get_location_ids( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'posts' )->where( 'post_type', 'wpseo_locations' );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select()->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Get all location IDs.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	private function get_video_posts( $count = false ) {
		global $wpdb;
		$paged = $this->get_pagination_arg( 'page' );
		$posts = get_posts(
			[
				'numberposts' => -1,
				'post_type'   => 'any',
				'post_status' => 'any',
				'fields'      => 'ids',
				'meta_query'  => [
					'relation' => 'AND',
					[
						'key'     => '_yoast_wpseo_video_meta',
						'compare' => 'EXISTS',
					],
					[
						'key'     => '_yoast_wpseo_videositemap-disable',
						'compare' => 'NOT EXISTS',
					],
				],
			]
		);

		return $count ? count( $posts ) : $posts;
	}

	/**
	 * Set post robots.
	 *
	 * @param int $post_id Post ID.
	 */
	private function set_post_robots( $post_id ) {
		// Early bail if robots data is set in Rank Math plugin.
		if ( ! empty( $this->get_meta( 'post', $post_id, 'rank_math_robots' ) ) ) {
			return;
		}

		$robots_nofollow = get_post_meta( $post_id, '_yoast_wpseo_meta-robots-nofollow', true );
		$robots_noindex  = (int) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-noindex', true );
		$robots_advanced = (array) get_post_meta( $post_id, '_yoast_wpseo_meta-robots-adv', true );

		// If all are empty, then keep default robots.
		if ( empty( $robots_nofollow ) && empty( $robots_noindex ) && empty( $robots_advanced ) ) {
			update_post_meta( $post_id, 'rank_math_robots', [] );
			return;
		}

		$robots = [ $this->set_robots_index( $post_id, $robots_noindex ) ];
		if ( $robots_nofollow ) {
			$robots[] = 'nofollow';
		}

		$robots_advanced = explode( ',', $robots_advanced[0] );
		if ( $robots_advanced ) {
			$robots = array_merge( $robots, $robots_advanced );
		}

		update_post_meta( $post_id, 'rank_math_robots', array_filter( array_unique( $robots ) ) );
	}

	/**
	 * Set post robots based on the Settings.
	 *
	 * @param int $post_id        Post ID.
	 * @param int $robots_noindex Whether or not the post is indexed.
	 *
	 * @return string
	 */
	private function set_robots_index( $post_id, $robots_noindex ) {
		if ( 0 === $robots_noindex ) {
			$yoast_titles = get_option( 'wpseo_titles' );
			return empty( $yoast_titles[ 'noindex-' . get_post_type( $post_id ) ] ) ? 'index' : 'noindex';
		}

		return 1 === $robots_noindex ? 'noindex' : 'index';
	}

	/**
	 * Set Focus Keyword.
	 *
	 * @param int $post_id Post ID.
	 */
	private function set_post_focus_keyword( $post_id ) {
		$extra_fks = get_post_meta( $post_id, '_yoast_wpseo_focuskeywords', true );
		$extra_fks = json_decode( $extra_fks, true );
		if ( empty( $extra_fks ) || ! is_array( $extra_fks ) ) {
			return;
		}

		$extra_fks = implode( ', ', array_column( $extra_fks, 'keyword' ) );
		$main_fk   = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		update_post_meta( $post_id, 'rank_math_focus_keyword', $main_fk . ', ' . $extra_fks );
	}

	/**
	 * Set primary term for the posts.
	 *
	 * @param int[] $post_ids Post IDs.
	 */
	private function set_primary_term( $post_ids ) {
		$post_ids = wp_list_pluck( $post_ids, 'ID' );
		$table    = DB::query_builder( 'postmeta' );
		$results  = $table->whereLike( 'meta_key', 'wpseo_primary' )->whereIn( 'post_id', $post_ids )->get();

		foreach ( $results as $result ) {
			$key = str_replace( '_yoast_wpseo', 'rank_math', $result->meta_key );
			update_post_meta( $result->post_id, $key, $result->meta_value );
		}
	}

	/**
	 * Import term meta of plugin.
	 *
	 * @return array
	 */
	protected function termmeta() {
		$count         = 0;
		$taxonomy_meta = get_option( 'wpseo_taxonomy_meta' );

		if ( empty( $taxonomy_meta ) ) {
			return compact( 'count' );
		}

		$hash = [
			'wpseo_title'                 => 'rank_math_title',
			'wpseo_desc'                  => 'rank_math_description',
			'wpseo_metadesc'              => 'rank_math_description',
			'wpseo_focuskw'               => 'rank_math_focus_keyword',
			'wpseo_canonical'             => 'rank_math_canonical_url',
			'wpseo_opengraph-title'       => 'rank_math_facebook_title',
			'wpseo_opengraph-description' => 'rank_math_facebook_description',
			'wpseo_twitter-title'         => 'rank_math_twitter_title',
			'wpseo_twitter-description'   => 'rank_math_twitter_description',
			'wpseo_bctitle'               => 'rank_math_breadcrumb_title',
		];
		foreach ( $taxonomy_meta as $terms ) {
			foreach ( $terms as $term_id => $data ) {
				$count++;
				delete_term_meta( $term_id, 'rank_math_permalink' );
				$this->replace_meta( $hash, $data, $term_id, 'term', 'convert_variables' );

				$this->set_term_robots( $term_id, $data );
				$this->set_term_social_media( $term_id, $data );
				$this->is_twitter_using_facebook( 'term', $term_id );
			}
		}

		return compact( 'count' );
	}

	/**
	 * Set term robots.
	 *
	 * @param int   $term_id Term ID.
	 * @param array $data    Term data.
	 */
	private function set_term_robots( $term_id, $data ) {
		// Early bail if robots data is set in Rank Math plugin.
		if ( ! empty( $this->get_meta( 'term', $term_id, 'rank_math_robots' ) ) ) {
			return;
		}

		if ( ! empty( $data['wpseo_noindex'] ) && 'default' !== $data['wpseo_noindex'] ) {
			$robots = 'noindex' === $data['wpseo_noindex'] ? 'noindex' : 'index';
			update_term_meta( $term_id, 'rank_math_robots', [ $robots ] );
		}
	}

	/**
	 * Set term social media.
	 *
	 * @param int   $term_id Term ID.
	 * @param array $data    Term data.
	 */
	private function set_term_social_media( $term_id, $data ) {
		if ( ! empty( $data['wpseo_opengraph-image'] ) ) {
			$this->replace_image( $data['wpseo_opengraph-image'], 'term', 'rank_math_facebook_image', 'rank_math_facebook_image_id', $term_id );
		}

		if ( ! empty( $data['wpseo_twitter-image'] ) ) {
			$this->replace_image( $data['wpseo_twitter-image'], 'term', 'rank_math_twitter_image', 'rank_math_twitter_image_id', $term_id );
		}
	}

	/**
	 * Import user meta of plugin.
	 *
	 * @return array
	 */
	protected function usermeta() {
		$this->set_pagination( $this->get_user_ids( true ) );
		$user_ids = $this->get_user_ids();

		$hash = [
			'wpseo_title'    => 'rank_math_title',
			'wpseo_desc'     => 'rank_math_description',
			'wpseo_metadesc' => 'rank_math_description',
		];

		foreach ( $user_ids as $user ) {
			$userid = $user->ID;
			$this->replace_meta( $hash, null, $userid, 'user', 'convert_variables' );

			// Early bail if robots data is set in Rank Math plugin.
			if ( empty( $this->get_meta( 'user', $userid, 'rank_math_robots' ) ) && get_user_meta( $userid, 'wpseo_noindex_author', true ) ) {
				update_user_meta( $userid, 'rank_math_robots', [ 'noindex' ] );
			}

			$social_urls = [];
			foreach ( [ 'linkedin', 'myspace', 'pinterest', 'instagram', 'soundcloud', 'tumblr', 'youtube', 'wikipedia' ] as $key ) {
				$social_urls[] = get_user_meta( $userid, $key, true );
			}

			if ( ! empty( $social_urls ) ) {
				update_user_meta( $userid, 'additional_profile_urls', implode( ' ', array_filter( $social_urls ) ) );
			}
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Imports redirections data.
	 *
	 * @return array
	 */
	protected function redirections() {
		$count        = 0;
		$redirections = get_option( 'wpseo-premium-redirects-base' );

		if ( ! $redirections ) {
			return compact( 'count' );
		}

		Helper::update_modules( [ 'redirections' => 'on' ] );
		foreach ( $redirections as $redirection ) {
			if ( false !== $this->save_redirection( $redirection ) ) {
				$count++;
			}
		}

		return compact( 'count' );
	}

	/**
	 * Save redirection.
	 *
	 * @param array $redirection Redirection object.
	 *
	 * @return mixed
	 */
	private function save_redirection( $redirection ) {
		if ( ! isset( $redirection['origin'] ) || empty( $redirection['origin'] ) ) {
			return false;
		}

		$item = Redirection::from(
			[
				'sources'     => [
					[
						'pattern'    => $redirection['origin'],
						'comparison' => isset( $redirection['format'] ) && 'regex' === $redirection['format'] ? 'regex' : 'exact',
					],
				],
				'url_to'      => isset( $redirection['url'] ) ? $redirection['url'] : '',
				'header_code' => isset( $redirection['type'] ) ? $redirection['type'] : '301',
			]
		);

		return $item->save();
	}

	/**
	 * Set additional Organization details.
	 *
	 * @param array $yoast_titles Settings.
	 */
	private function set_additional_organization_details( $yoast_titles ) {
		$additional_details = [];
		$properties         = [
			'org-legal-name'       => 'legalName',
			'org-founding-date'    => 'foundingDate',
			'org-number-employees' => 'numberOfEmployees',
			'org-vat-id'           => 'vatID',
			'org-tax-id'           => 'taxID',
			'org-iso'              => 'iso6523Code',
			'org-duns'             => 'duns',
			'org-leicode'          => 'leiCode',
			'org-naics'            => 'naics',
		];

		foreach ( $properties as $key => $property ) {
			if ( empty( $yoast_titles[ $key ] ) ) {
				continue;
			}

			$additional_details[] = [
				'type'  => $property,
				'value' => $yoast_titles[ $key ],
			];
		}

		if ( ! empty( $additional_details ) ) {
			$this->titles['additional_info'] = $additional_details;
		}
	}

	/**
	 * Set separator.
	 *
	 * @param array $yoast_titles    Settings.
	 */
	private function set_separator( $yoast_titles ) {
		if ( ! isset( $yoast_titles['separator'] ) ) {
			return;
		}

		$separator_options = [
			'sc-dash'   => '-',
			'sc-ndash'  => '&ndash;',
			'sc-mdash'  => '&mdash;',
			'sc-middot' => '&middot;',
			'sc-bull'   => '&bull;',
			'sc-star'   => '*',
			'sc-smstar' => '&#8902;',
			'sc-pipe'   => '|',
			'sc-tilde'  => '~',
			'sc-laquo'  => '&laquo;',
			'sc-raquo'  => '&raquo;',
			'sc-lt'     => '&lt;',
			'sc-gt'     => '&gt;',
		];

		if ( isset( $separator_options[ $yoast_titles['separator'] ] ) ) {
			$this->titles['title_separator'] = $separator_options[ $yoast_titles['separator'] ];
		}
	}

	/**
	 * Misc settings.
	 *
	 * @param array $yoast_titles Settings.
	 * @param array $yoast_social Settings.
	 */
	private function misc_settings( $yoast_titles, $yoast_social ) {
		$knowledgegraph_type = ! empty( $yoast_titles['company_or_person'] ) ? $yoast_titles['company_or_person'] : '';

		$logo_key = 'company' === $knowledgegraph_type ? 'company_logo' : 'person_logo';
		$logo_id  = 'company' === $knowledgegraph_type ? 'company_logo_id' : 'person_logo_id';

		$hash = [
			'company_name'           => 'knowledgegraph_name',
			'website_name'           => 'website_name',
			'alternate_website_name' => 'website_alternate_name',
			'company_or_person'      => 'knowledgegraph_type',
			$logo_key                => 'knowledgegraph_logo',
			$logo_id                 => 'knowledgegraph_logo_id',
		];
		$this->replace( $hash, $yoast_titles, $this->titles );

		$this->replace( [ 'stripcategorybase' => 'strip_category_base' ], $yoast_titles, $this->settings, 'convert_bool' );
		$this->replace( [ 'disable-attachment' => 'attachment_redirect_urls' ], $yoast_titles, $this->settings, 'convert_bool' );
		$this->replace( [ 'disable-author' => 'disable_author_archives' ], $yoast_titles, $this->titles, 'convert_bool' );
		$this->replace( [ 'disable-date' => 'disable_date_archives' ], $yoast_titles, $this->titles, 'convert_bool' );// Links.

		// NOINDEX.
		$hash = [
			'noindex-subpages-wpseo' => 'noindex_archive_subpages',
		];
		$this->replace( $hash, $yoast_titles, $this->titles, 'convert_bool' );

		// OpenGraph.
		if ( isset( $yoast_social['og_default_image'] ) ) {
			$this->replace_image( $yoast_social['og_default_image'], $this->titles, 'open_graph_image', 'open_graph_image_id' );
		}

		if ( isset( $yoast_social['og_frontpage_image'] ) ) {
			$this->replace_image( $yoast_social['og_frontpage_image'], $this->titles, 'homepage_facebook_image', 'homepage_facebook_image_id' );
		}

		$hash = [
			'og_frontpage_title' => 'homepage_facebook_title',
			'og_frontpage_desc'  => 'homepage_facebook_description',
		];
		$this->replace( $hash, $yoast_social, $this->titles, 'convert_variables' );

		if ( ! empty( $yoast_titles['noindex-author-wpseo'] ) ) {
			$this->titles['author_custom_robots'] = 'on';
			$this->titles['author_robots'][]      = 'noindex';
		}

		if ( ! empty( $yoast_titles['disable-attachment'] ) ) {
			$this->titles['pt_attachment_robots'][] = 'noindex';
		}
	}

	/**
	 * Slack enhanced sharing.
	 *
	 * @param array $yoast_main Settings.
	 */
	private function slack_settings( $yoast_main ) {
		$slack_enhanced_sharing = 'off';
		if ( ! empty( $yoast_main['enable_enhanced_slack_sharing'] ) ) {
			$slack_enhanced_sharing = 'on';
		}
		$this->titles['pt_post_slack_enhanced_sharing']     = $slack_enhanced_sharing;
		$this->titles['pt_page_slack_enhanced_sharing']     = $slack_enhanced_sharing;
		$this->titles['pt_product_slack_enhanced_sharing']  = $slack_enhanced_sharing;
		$this->titles['pt_download_slack_enhanced_sharing'] = $slack_enhanced_sharing;
		$this->titles['author_slack_enhanced_sharing']      = $slack_enhanced_sharing;
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$this->titles[ 'tax_' . $taxonomy . '_slack_enhanced_sharing' ] = $slack_enhanced_sharing;
		}
	}

	/**
	 * Sitemap settings.
	 *
	 * @param array $yoast_main    Settings.
	 * @param array $yoast_sitemap Settings.
	 */
	private function sitemap_settings( $yoast_main, $yoast_sitemap ) {
		if ( ! isset( $yoast_main['enable_xml_sitemap'] ) && isset( $yoast_sitemap['enablexmlsitemap'] ) ) {
			Helper::update_modules( [ 'sitemap' => 'on' ] );
		}

		$hash = [
			'entries-per-page' => 'items_per_page',
			'excluded-posts'   => 'exclude_posts',
		];
		$this->replace( $hash, $yoast_sitemap, $this->sitemap );

		if ( empty( $yoast_sitemap['excluded-posts'] ) ) {
			$this->sitemap['exclude_posts'] = '';
		}

		$this->sitemap_exclude_roles( $yoast_sitemap );
	}

	/**
	 * Import News Settings from Yoast News plugin.
	 */
	protected function news() {
		$yoast_news = get_option( 'wpseo_news' );
		if ( empty( $yoast_news ) ) {
			return false;
		}

		Helper::update_modules( [ 'news-sitemap' => 'on' ] );

		$this->get_settings();
		$this->sitemap['news_sitemap_publication_name'] = ! empty( $yoast_news['news_sitemap_name'] ) ? $yoast_news['news_sitemap_name'] : '';
		if ( ! empty( $yoast_news['news_sitemap_include_post_types'] ) ) {
			$this->sitemap['news_sitemap_post_type'] = array_keys( $yoast_news['news_sitemap_include_post_types'] );
			$this->add_excluded_news_terms( $yoast_news );
		}
		$this->update_settings();

		return true;
	}

	/**
	 * Import News Settings from Yoast News plugin.
	 */
	protected function video() {
		$yoast_video = get_option( 'wpseo_video' );
		if ( empty( $yoast_video ) ) {
			return false;
		}

		Helper::update_modules( [ 'video-sitemap' => 'on' ] );

		$this->get_settings();
		$this->sitemap['hide_video_sitemap']          = ! empty( $yoast_video['video_cloak_sitemap'] ) ? 'on' : '';
		$this->sitemap['video_sitemap_post_type']     = ! empty( $yoast_video['videositemap_posttypes'] ) ? array_keys( $yoast_video['videositemap_posttypes'] ) : [];
		$this->sitemap['video_sitemap_taxonomies']    = ! empty( $yoast_video['videositemap_taxonomies'] ) ? array_keys( $yoast_video['videositemap_taxonomies'] ) : [];
		$this->sitemap['video_sitemap_custom_fields'] = ! empty( $yoast_video['video_custom_fields'] ) ? $yoast_video['video_custom_fields'] : '';
		$this->settings['disable_media_rss']          = ! empty( $yoast_video['video_disable_rss'] ) ? $yoast_video['video_disable_rss'] : '';
		$this->update_settings();

		$this->set_pagination( $this->get_video_posts( true ) );
		$videos = $this->get_video_posts();
		$schema = [
			'@type'    => 'VideoObject',
			'metadata' => [
				'type'      => 'template',
				'isPrimary' => false,
				'title'     => 'Video',
				'shortcode' => uniqid( 's-' ),
			],
		];

		$meta = [
			'category' => '_yoast_wpseo_videositemap-category',
			'tags'     => '_yoast_wpseo_videositemap-tags',
			'rating'   => '_yoast_wpseo_videositemap-rating',
		];
		foreach ( $videos as $video ) {
			$yoast_video = get_post_meta( $video, '_yoast_wpseo_video_meta', true );
			$duration    = get_post_meta( $video, '_yoast_wpseo_videositemap-duration', true );
			$thumbnail   = get_post_meta( $video, '_yoast_wpseo_videositemap-thumbnail', true );
			$entity      = [
				'name'             => '%seo_title%',
				'description'      => '%seo_description%',
				'thumbnailUrl'     => $thumbnail ? $thumbnail : '%post_thumbnail%',
				'contentUrl'       => ! empty( $yoast_video['content_loc'] ) ? $yoast_video['content_loc'] : '',
				'embedUrl'         => ! empty( $yoast_video['player_loc'] ) ? $yoast_video['player_loc'] : '',
				'width'            => ! empty( $yoast_video['width'] ) ? $yoast_video['width'] : '',
				'height'           => ! empty( $yoast_video['height'] ) ? $yoast_video['height'] : '',
				'isFamilyFriendly' => ! get_post_meta( $video, '_yoast_wpseo_videositemap-not-family-friendly', true ),
				'duration'         => $duration ? $duration . 'S' : '',
				'uploadDate'       => '%date(Y-m-d\TH:i:sP)%',
			];

			foreach ( $meta as $key => $yoast_key ) {
				$schema['metadata'][ $key ] = get_post_meta( $video, $yoast_key, true );
			}

			$schema = array_merge( $schema, $entity );

			update_post_meta( $video, 'rank_math_schema_VideoObject', array_merge( $schema, $entity ) );
		}

		return true;
	}

	/**
	 * Deactivate plugin action.
	 */
	protected function deactivate() {
		if ( is_plugin_active( $this->get_plugin_file() ) ) {
			deactivate_plugins( $this->get_plugin_file() );
			deactivate_plugins( 'wpseo-news/wpseo-news.php' );
			deactivate_plugins( 'wpseo-video/video-seo.php' );
			deactivate_plugins( 'wpseo-local/local-seo.php' );
		}

		return true;
	}

	/**
	 * Import Excluded News terms.
	 *
	 * @param array $yoast_news News Sitemap Settings.
	 */
	private function add_excluded_news_terms( $yoast_news ) {
		$exclude_terms = $yoast_news['news_sitemap_exclude_terms'];
		if ( empty( $exclude_terms ) ) {
			return;
		}

		$post_types = array_keys( $yoast_news['news_sitemap_include_post_types'] );
		foreach ( $post_types as $post_type ) {
			$taxonomies   = get_object_taxonomies( $post_type, 'objects' );
			$exclude_data = [];
			foreach ( $taxonomies as $taxonomy ) {
				if ( ! $taxonomy->show_ui ) {
					continue;
				}

				$terms = get_terms(
					[
						'taxonomy'   => $taxonomy->name,
						'hide_empty' => false,
						'fields'     => 'id=>slug',
					]
				);

				if ( empty( $terms ) ) {
					continue;
				}

				foreach ( $terms as $term_id => $term ) {
					$field = "{$taxonomy->name}_{$term}_for_{$post_type}";
					$key   = "news_sitemap_exclude_{$post_type}_terms";
					if ( isset( $exclude_terms[ $field ] ) && 'on' === $exclude_terms[ $field ] ) {
						$exclude_data[ $taxonomy->name ][] = $term_id;
					}
				}
			}

			if ( ! empty( $exclude_data ) ) {
				$this->sitemap[ "news_sitemap_exclude_{$post_type}_terms" ] = [ $exclude_data ];
			}
		}
	}

	/**
	 * Sitemap exclude roles.
	 *
	 * @param array $yoast_sitemap Settings.
	 */
	private function sitemap_exclude_roles( $yoast_sitemap ) {
		foreach ( Helper::get_roles() as $role => $label ) {
			$key = "user_role-{$role}-not_in_sitemap";
			if ( isset( $yoast_sitemap[ $key ] ) && $yoast_sitemap[ $key ] ) {
				$this->sitemap['exclude_roles'][] = $role;
			}
		}

		if ( ! empty( $this->sitemap['exclude_roles'] ) ) {
			$this->sitemap['exclude_roles'] = array_unique( $this->sitemap['exclude_roles'] );
		}
	}

	/**
	 * Local SEO settings.
	 */
	private function local_seo_settings() {
		$yoast_local = get_option( 'wpseo_local', false );
		if ( ! is_array( $yoast_local ) ) {
			return;
		}

		$this->titles['local_seo'] = 'on';
		$this->local_address_settings( $yoast_local );
		$this->local_phones_settings( $yoast_local );

		if ( ! empty( $yoast_local['location_address_2'] ) ) {
			$this->titles['local_address']['streetAddress'] .= ' ' . $yoast_local['location_address_2'];
		}

		// Coordinates.
		if ( ! empty( $yoast_local['location_coords_lat'] ) && ! empty( $yoast_local['location_coords_long'] ) ) {
			$this->titles['geo'] = $yoast_local['location_coords_lat'] . ' ' . $yoast_local['location_coords_long'];
		}

		// Opening Hours.
		if ( ! empty( $yoast_local['opening_hours_24h'] ) ) {
			$this->titles['opening_hours_format'] = isset( $yoast_local['opening_hours_24h'] ) && 'on' === $yoast_local['opening_hours_24h'] ? 'off' : 'on';
		}
	}

	/**
	 * Local phones settings.
	 *
	 * @param array $yoast_local Array of yoast local SEO settings.
	 */
	private function local_phones_settings( $yoast_local ) {
		if ( empty( $yoast_local['location_phone'] ) ) {
			return;
		}

		$this->titles['phone_numbers'][] = [
			'type'   => 'customer support',
			'number' => $yoast_local['location_phone'],
		];

		if ( ! empty( $yoast_local['location_phone_2nd'] ) ) {
			$this->titles['phone_numbers'][] = [
				'type'   => 'customer support',
				'number' => $yoast_local['location_phone_2nd'],
			];
		}
	}

	/**
	 * Local address settings.
	 *
	 * @param array $yoast_local Array of yoast local SEO settings.
	 */
	private function local_address_settings( $yoast_local ) {
		// Address Format.
		$address_format_hash = [
			'address-state-postal'       => '{address} {locality}, {region} {postalcode}',
			'address-state-postal-comma' => '{address} {locality}, {region}, {postalcode}',
			'address-postal-city-state'  => '{address} {postalcode} {locality}, {region}',
			'address-postal'             => '{address} {locality} {postalcode}',
			'address-postal-comma'       => '{address} {locality}, {postalcode}',
			'address-city'               => '{address} {locality}',
			'postal-address'             => '{postalcode} {region} {locality} {address}',
		];

		$this->titles['local_address_format'] = $address_format_hash[ $yoast_local['address_format'] ];

		// Street Address.
		$address = [];
		$hash    = [
			'location_address' => 'streetAddress',
			'location_city'    => 'addressLocality',
			'location_state'   => 'addressRegion',
			'location_zipcode' => 'postalCode',
			'location_country' => 'addressCountry',
		];
		$this->replace( $hash, $yoast_local, $address );

		$this->titles['local_address'] = $address;
	}

	/**
	 * Social and Webmaster settings.
	 *
	 * @param array $yoast_main   Settings.
	 * @param array $yoast_social Settings.
	 */
	private function social_webmaster_settings( $yoast_main, $yoast_social ) {
		$hash = [
			'baiduverify'     => 'baidu_verify',
			'googleverify'    => 'google_verify',
			'msverify'        => 'bing_verify',
			'pinterestverify' => 'pinterest_verify',
			'yandexverify'    => 'yandex_verify',
		];
		$this->replace( $hash, $yoast_main, $this->settings );

		$hash = [
			'facebook_site' => 'social_url_facebook',
			'twitter_site'  => 'twitter_author_names',
			'fbadminapp'    => 'facebook_app_id',
		];

		if ( ! empty( $yoast_social['other_social_urls'] ) ) {
			$this->titles['social_additional_profiles'] = implode( PHP_EOL, $yoast_social['other_social_urls'] );
		}
		$this->replace( $hash, $yoast_social, $this->titles );
	}

	/**
	 * Breadcrumb settings.
	 *
	 * @param array $yoast_titles        Settings.
	 * @param array $yoast_internallinks Settings.
	 */
	private function breadcrumb_settings( $yoast_titles, $yoast_internallinks ) {
		$hash = [
			'breadcrumbs-sep'           => 'breadcrumbs_separator',
			'breadcrumbs-home'          => 'breadcrumbs_home_label',
			'breadcrumbs-prefix'        => 'breadcrumbs_prefix',
			'breadcrumbs-archiveprefix' => 'breadcrumbs_archive_format',
			'breadcrumbs-searchprefix'  => 'breadcrumbs_search_format',
			'breadcrumbs-404crumb'      => 'breadcrumbs_404_label',
		];
		$this->replace( $hash, $yoast_titles, $this->settings );
		$this->replace( $hash, $yoast_internallinks, $this->settings );

		$hash = [ 'breadcrumbs-enable' => 'breadcrumbs' ];
		$this->replace( $hash, $yoast_titles, $this->settings, 'convert_bool' );
		$this->replace( $hash, $yoast_internallinks, $this->settings, 'convert_bool' );

		// RSS.
		$hash = [
			'rssbefore' => 'rss_before_content',
			'rssafter'  => 'rss_after_content',
		];
		$this->replace( $hash, $yoast_titles, $this->settings, 'convert_variables' );
	}

	/**
	 * Import/convert blocks of plugin.
	 *
	 * @return array
	 */
	protected function blocks() {
		$posts = Yoast_Blocks::get()->find_posts();
		if ( empty( $posts['posts'] ) ) {
			return __( 'No post found.', 'rank-math' );
		}

		$this->set_pagination( $posts['count'] );

		Yoast_Blocks::get()->wizard( array_slice( $posts['posts'], ( $this->items_per_page * ( $this->get_pagination_arg( 'page' ) - 1 ) ), $this->items_per_page ) );

		return $this->get_pagination_arg();
	}
}
