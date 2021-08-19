<?php
/**
 * The AIO SEO Import Class
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Redirections\Redirection;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\DB;

defined( 'ABSPATH' ) || exit;

/**
 * AIOSEO class.
 */
class AIOSEO extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'All In One SEO Pack';

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key = '_aioseop_';

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys = [ 'aioseo_options' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'settings', 'postmeta', 'termmeta', 'redirections', 'locations' ];

	/**
	 * Get the actions which can be performed for the plugin.
	 *
	 * @return array
	 */
	public function get_choices() {
		$choices = [
			'settings' => esc_html__( 'Import Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import AIO SEO plugin settings, global meta, sitemap settings, etc.', 'rank-math' ) ),
			'postmeta' => esc_html__( 'Import Post Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information of your posts/pages like the titles, descriptions, robots meta, OpenGraph info, etc.', 'rank-math' ) ),
		];

		if ( DB::check_table_exists( 'aioseo_terms' ) ) {
			$choices['termmeta'] = esc_html__( 'Import Term Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information of your terms like the titles, descriptions, robots meta, OpenGraph info, etc.', 'rank-math' ) );
		}

		if ( DB::check_table_exists( 'aioseo_redirects' ) ) {
			$choices['redirections'] = esc_html__( 'Import Redirections', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import all the redirections you have already set up in AIO SEO Premium.', 'rank-math' ) );
		}

		if ( ! empty( $this->get_location_ids( true ) ) ) {
			$choices['locations'] = esc_html__( 'Import Locations', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import Locations Settings.', 'rank-math' ) );
		}

		return $choices;
	}

	/**
	 * Convert Yoast / AIO SEO variables if needed.
	 *
	 * @param string $string Value to convert.
	 * @return string
	 */
	public function convert_variables( $string ) {
		$string = str_replace( '#site_title', '%sitename%', $string );
		$string = str_replace( '#tagline', '%sitedesc%', $string );
		$string = str_replace( '#separator_sa', '%sep%', $string );
		$string = str_replace( '#post_title', '%title%', $string );
		$string = str_replace( '#post_excerpt', '%excerpt%', $string );
		$string = str_replace( '#post_content', '%excerpt%', $string );
		$string = str_replace( '#taxonomy_description', '%term_description%', $string );
		$string = str_replace( '#category_description', '%term_description%', $string );
		$string = str_replace( '#taxonomy_title', '%term%', $string );
		$string = str_replace( '#category', '%term%', $string );
		$string = str_replace( '#author_first_name #author_last_name', '%name%', $string );
		$string = str_replace( '#current_date', '%currentdate%', $string );
		$string = str_replace( '#current_day', '%currentday%', $string );
		$string = str_replace( '#current_month', '%currentmonth%', $string );
		$string = str_replace( '#post_date', '%date%', $string );
		$string = str_replace( '#search_term', '%search_query%', $string );
		$string = str_replace( '#author_link', '%AUTHORLINK%', $string );
		$string = str_replace( '#post_link', '%POSTLINK%', $string );
		$string = str_replace( '#site_link', '%BLOGLINK%', $string );
		$string = str_replace( '#author_name', '%name%', $string );
		$string = str_replace( '#author_bio', '%user_description%', $string );
		$string = str_replace( '#archive_date', '%date%', $string );
		$string = str_replace( '#breadcrumb_archive_post_type_name', '%s', $string );
		$string = str_replace( '#breadcrumb_search_string', '%s', $string );

		return $string;
	}

	/**
	 * Import settings of plugin.
	 *
	 * @return bool
	 */
	protected function settings() {
		$this->get_settings();
		$this->aio_settings = json_decode( get_option( 'aioseo_options' ), true );

		$this->general_settings();
		$this->sitemap_settings();
		$this->titles_settings();
		$this->pro_settings();
		$this->update_settings();

		return true;
	}

	/**
	 * Imports redirections data.
	 *
	 * @return array
	 */
	protected function redirections() {
		$count        = 0;
		$table        = DB::query_builder( 'aioseo_redirects' );
		$redirections = $table->select( '*' )->get();

		if ( empty( $redirections ) ) {
			return compact( 'count' );
		}

		Helper::update_modules( [ 'redirections' => 'on' ] );
		foreach ( $redirections as $redirection ) {
			if ( false !== $this->save_redirection( (array) $redirection ) ) {
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
		if (
			empty( $redirection['source_url'] ) ||
			empty( $redirection['type'] ) ||
			! in_array( $redirection['type'], [ '301', '302', '307', '410', '451' ], true )
		) {
			return false;
		}

		$item = Redirection::from(
			[
				'sources'     => [
					[
						'ignore'     => ! empty( $redirection['ignore_case'] ) ? 'case' : '',
						'pattern'    => $redirection['source_url'],
						'comparison' => isset( $redirection['source_url_match'] ) && 'regex' === $redirection['source_url_match'] ? 'regex' : 'exact',
					],
				],
				'url_to'      => isset( $redirection['target_url'] ) ? $redirection['target_url'] : '',
				'header_code' => isset( $redirection['type'] ) ? $redirection['type'] : '301',
				'status'      => empty( $redirection['enabled'] ) ? 'inactive' : 'active',
			]
		);

		return $item->save();
	}

	/**
	 * Import General Settings.
	 */
	private function general_settings() {
		if ( ! empty( $this->aio_settings['rssContent'] ) ) {
			$hash = [
				'before' => 'rss_before_content',
				'after'  => 'rss_after_content',
			];
			$this->replace( $hash, $this->aio_settings['rssContent'], $this->settings, 'convert_variables' );
		}

		if ( ! empty( $this->aio_settings['breadcrumbs'] ) ) {
			$hash = [
				'enable'             => 'breadcrumbs',
				'separator'          => 'breadcrumbs_separator',
				'homepageLabel'      => 'breadcrumbs_home_label',
				'breadcrumbPrefix'   => 'breadcrumbs_prefix',
				'archiveFormat'      => 'breadcrumbs_archive_format',
				'searchResultFormat' => 'breadcrumbs_search_format',
				'errorFormat404'     => 'breadcrumbs_404_label',
				'showCurrentItem'    => 'breadcrumbs_remove_post_title',
			];
			$this->replace( $hash, $this->aio_settings['breadcrumbs'], $this->settings, 'convert_variables' );
			$this->replace( [ 'homepageLink' => 'breadcrumbs_home' ], $this->aio_settings['breadcrumbs'], $this->settings, 'convert_bool' );
		}

		if ( ! empty( $this->aio_settings['webmasterTools'] ) ) {
			$hash = [
				'google'    => 'google_verify',
				'bing'      => 'bing_verify',
				'baidu'     => 'baidu_verify',
				'yandex'    => 'yandex_verify',
				'pinterest' => 'pinterest_verify',
			];
			$this->replace( $hash, $this->aio_settings['webmasterTools'], $this->settings );
		}
	}

	/**
	 * Sitemap settings.
	 */
	private function sitemap_settings() {
		if ( empty( $this->aio_settings['sitemap'] ) ) {
			return;
		}

		$sitemap_settings = $this->aio_settings['sitemap'];
		$general          = ! empty( $sitemap_settings['general'] ) ? $sitemap_settings['general'] : [];
		if ( empty( $general ) ) {
			return;
		}

		// Sitemap.
		if ( isset( $general['enable'] ) ) {
			Helper::update_modules( [ 'sitemap' => 'on' ] );
		}

		$this->sitemap['items_per_page'] = $general['linksPerIndex'];

		$all = ! empty( $general['postTypes']['all'] );
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$this->sitemap[ "pt_{$post_type}_sitemap" ] = $all || in_array( $post_type, $general['postTypes']['included'], true ) ? 'on' : 'off';
		}

		$all = ! empty( $general['taxonomies']['all'] );
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$this->sitemap[ "tax_{$taxonomy}_sitemap" ] = $all || in_array( $taxonomy, $general['taxonomies']['included'], true ) ? 'on' : 'off';
		}

		if ( ! empty( $general['advancedSettings'] ) ) {
			$this->sitemap_advanced_settings( $general['advancedSettings'] );
		}
	}

	/**
	 * Import Pro Settings.
	 */
	private function pro_settings() {
		$settings = get_option( 'aioseo_options_pro' );
		if ( empty( $settings ) ) {
			return;
		}

		$settings = json_decode( $settings, true );
		$this->news_sitemap_settings( $settings );
		$this->video_sitemap_settings( $settings );
		$this->image_seo_settings( $settings );
		$this->local_seo_settings( $settings );
	}

	/**
	 * Import Local SEO Settings.
	 *
	 * @param array $settings Pro Settings.
	 */
	private function local_seo_settings( $settings ) {
		if ( empty( $settings['localBusiness'] ) ) {
			return;
		}

		Helper::update_modules( [ 'local-seo' => 'on' ] );
		$local_settings = $settings['localBusiness'];
		$business       = $local_settings['locations']['business'];

		$hash = [
			'name'         => 'knowledgegraph_name',
			'image'        => 'knowledgegraph_logo',
			'businessType' => 'local_business_type',
		];
		$this->replace( $hash, $business, $this->titles );
		$this->titles['url'] = $business['urls']['website'];

		// Street Address.
		$address = [];
		$hash    = [
			'streetLine1' => 'streetAddress',
			'city'        => 'addressLocality',
			'state'       => 'addressRegion',
			'zipCode'     => 'postalCode',
			'country'     => 'addressCountry',
		];
		$this->replace( $hash, $business['address'], $address );
		if ( ! empty( $business['address']['streetLine2'] ) ) {
			$address['streetAddress'] = $address['streetAddress'] . ', ' . $business['address']['streetLine2'];
		}
		$this->titles['local_address'] = $address;

		if ( ! empty( $business['contact'] ) ) {
			$this->titles['email'] = $business['contact']['email'];

			$this->titles['phone_numbers'][] = [
				'type'   => 'customer support',
				'number' => $business['contact']['phone'],
			];
		}

		if ( ! empty( $business['payment'] ) ) {
			$this->titles['price_range'] = $business['payment']['priceRange'];
		}

		if ( ! empty( $local_settings['openingHours'] ) ) {
			$this->add_opening_hours( $local_settings['openingHours'] );
		}
	}

	/**
	 * Import Opening Hours Settings.
	 *
	 * @param array $opening_hours Opening Hours.
	 */
	private function add_opening_hours( $opening_hours ) {
		$data = [];
		foreach ( $opening_hours['days'] as $day => $opening_hour ) {
			if ( ! empty( $opening_hour['closed'] ) ) {
				continue;
			}

			$data[] = [
				'day'  => ucfirst( $day ),
				'time' => $opening_hour['openTime'] . '-' . $opening_hour['closeTime'],
			];
		}

		$this->titles['opening_hours'] = $data;
	}

	/**
	 * Import Image SEO Settings.
	 *
	 * @param array $settings Pro Settings.
	 */
	private function image_seo_settings( $settings ) {
		if ( empty( $settings['image']['format'] ) ) {
			return;
		}

		Helper::update_modules( [ 'image-seo' => 'on' ] );
		$format = $settings['image']['format'];
		if ( ! empty( $format['title'] ) ) {
			$this->settings['add_img_title'] = $this->convert_variables( $format['title'] );
		}

		if ( ! empty( $format['altTag'] ) ) {
			$this->settings['add_img_title'] = $this->convert_variables( $format['altTag'] );
		}
	}

	/**
	 * Import Video Sitemap Settings.
	 *
	 * @param array $settings Pro Settings.
	 */
	private function video_sitemap_settings( $settings ) {
		if ( empty( $settings['sitemap']['video'] ) ) {
			return;
		}
		$video_settings = $settings['sitemap']['video'];
		if ( ! empty( $video_settings['enable'] ) ) {
			Helper::update_modules( [ 'video-sitemap' => 'on' ] );
		}

		$this->sitemap['video_sitemap_post_type'] = $video_settings['postTypes']['all'] ? array_keys( Helper::get_accessible_post_types() ) : $video_settings['postTypes']['included'];
	}

	/**
	 * Import News Sitemap Settings.
	 *
	 * @param array $settings Pro Settings.
	 */
	private function news_sitemap_settings( $settings ) {
		if ( empty( $settings['sitemap']['news'] ) ) {
			return;
		}
		$news_settings = $settings['sitemap']['news'];
		if ( ! empty( $news_settings['enable'] ) ) {
			Helper::update_modules( [ 'news-sitemap' => 'on' ] );
		}

		$this->sitemap['news_sitemap_publication_name'] = ! empty( $news_settings['publicationName'] ) ? $news_settings['publicationName'] : '';
		$this->sitemap['news_sitemap_post_type']        = $news_settings['postTypes']['all'] ? array_keys( Helper::get_accessible_post_types() ) : $news_settings['postTypes']['included'];
	}

	/**
	 * Import Sitemap Advanced Settings.
	 *
	 * @param array $settings Sitemap Settings.
	 */
	private function sitemap_advanced_settings( $settings ) {
		if ( ! empty( $settings['excludePosts'] ) ) {
			$exclude_posts = [];
			foreach ( $settings['excludePosts'] as $exclude_post ) {
				$exclude_post    = json_decode( $exclude_post, true );
				$exclude_posts[] = ! empty( $exclude_post['value'] ) ? $exclude_post['value'] : '';
			}

			$this->sitemap['exclude_posts'] = implode( ', ', $exclude_posts );
		}

		if ( ! empty( $settings['excludeTerms'] ) ) {
			$exclude_terms = [];
			foreach ( $settings['excludeTerms'] as $exclude_term ) {
				$exclude_term    = json_decode( $exclude_term, true );
				$exclude_terms[] = ! empty( $exclude_term['value'] ) ? $exclude_term['value'] : '';
			}

			$this->sitemap['exclude_terms'] = $exclude_terms;
		}

		$this->sitemap['include_images'] = ! empty( $settings['excludeImages'] ) ? 'off' : 'on';
	}

	/**
	 * Import Titles & Meta Settings.
	 */
	private function titles_settings() {
		$settings = $this->aio_settings['searchAppearance'];
		$hash     = [
			'separator'       => 'title_separator',
			'siteTitle'       => 'homepage_title',
			'metaDescription' => 'homepage_description',
		];

		$this->replace( $hash, $settings['global'], $this->titles, 'convert_variables' );

		$this->titles['noindex_empty_taxonomies'] = $settings['advanced']['noIndexEmptyCat'];
		$this->titles['knowledgegraph_type']      = 'organization' === $settings['global']['schema']['siteRepresents'] ? 'company' : 'person';
		$this->titles['knowledgegraph_name']      = $settings['global']['schema']['organizationName'];
		$this->titles['knowledgegraph_logo']      = $settings['global']['schema']['organizationLogo'];

		$robots = $this->get_robots_data( $settings['advanced']['globalRobotsMeta'] );

		$this->titles['robots_global']          = $robots['robots'];
		$this->titles['advanced_robots_global'] = $robots['advanced_robots'];

		$this->social_settings();
		$this->archive_settings();
		$this->post_types_settings();
		$this->taxonomies_settings();
	}

	/**
	 * Import Social Settings.
	 */
	private function social_settings() {
		if ( empty( $this->aio_settings['social'] ) ) {
			return;
		}

		$hash = [
			'adminId'   => 'facebook_admin_id',
			'appId'     => 'facebook_app_id',
			'authorUrl' => 'facebook_author_urls',
		];
		$this->replace( $hash, $this->aio_settings['social']['facebook']['advanced'], $this->titles );

		$hash = [
			'title'       => 'homepage_facebook_title',
			'description' => 'homepage_facebook_description',
			'image'       => 'homepage_facebook_image',
		];
		$this->replace( $hash, $this->aio_settings['social']['facebook']['homePage'], $this->titles );
	}

	/**
	 * Archive settings.
	 */
	private function archive_settings() {
		$settings = $this->aio_settings['searchAppearance']['archives'];
		$author   = $settings['author'];
		$hash     = [
			'title'           => 'author_archive_title',
			'metaDescription' => 'author_archive_description',
		];
		$this->replace( $hash, $author, $this->titles, 'convert_variables' );
		$this->titles['disable_author_archives'] = $author['show'] ? 'off' : 'on';
		$this->titles['author_add_meta_box']     = $author['advanced']['showMetaBox'];
		$this->titles['author_custom_robots']    = ! $author['advanced']['robotsMeta']['default'];
		$robots                                  = $this->get_robots_data( $author['advanced']['robotsMeta'] );
		$this->titles['author_robots']           = $robots['robots'];
		$this->titles['author_advanced_robots']  = $robots['advanced_robots'];

		$date = $settings['date'];
		$hash = [
			'title'           => 'date_archive_title',
			'metaDescription' => 'date_archive_description',
		];
		$this->replace( $hash, $date, $this->titles, 'convert_variables' );
		$this->titles['disable_date_archives'] = $date['show'] ? 'off' : 'on';
		$robots                                = $this->get_robots_data( $date['advanced']['robotsMeta'] );
		$this->titles['date_archive_robots']   = $robots['robots'];
		$this->titles['date_advanced_robots']  = $robots['advanced_robots'];

		$this->titles['search_title'] = $this->convert_variables( $settings['search']['title'] );
	}

	/**
	 * Post Types settings.
	 */
	private function post_types_settings() {
		$settings = $this->aio_settings['searchAppearance']['dynamic']['postTypes'];
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( empty( $settings[ $post_type ] ) ) {
				continue;
			}

			$hash = [
				'title'           => "pt_{$post_type}_title",
				'metaDescription' => "pt_{$post_type}_description",
				'customFields'    => "pt_{$post_type}_analyze_fields",
			];
			$this->replace( $hash, $settings[ $post_type ], $this->titles, 'convert_variables' );

			if ( ! empty( $settings[ $post_type ]['schemaType'] ) ) {
				$schema_type = strtolower( $settings[ $post_type ]['schemaType'] );
				if ( in_array( $schema_type, [ 'article', 'book', 'course', 'dataset', 'event', 'jobposting', 'movie', 'music', 'person', 'product', 'recipe', 'restaurant', 'service', 'software', 'video' ], true ) ) {
					$this->titles[ "pt_{$post_type}_default_rich_snippet" ] = $schema_type;
					$this->titles[ "pt_{$post_type}_default_article_type" ] = $settings[ $post_type ]['articleType'];
				}
			}

			$robots = $settings[ $post_type ]['advanced']['robotsMeta'];
			if ( ! empty( $robots['default'] ) ) {
				$this->titles[ "pt_{$post_type}_custom_robots" ] = 'off';
				continue;
			}

			$robots = $this->get_robots_data( $robots );
			$this->titles[ "pt_{$post_type}_custom_robots" ]   = 'on';
			$this->titles[ "pt_{$post_type}_robots" ]          = $robots['robots'];
			$this->titles[ "pt_{$post_type}_advanced_robots" ] = $robots['advanced_robots'];
		}
	}

	/**
	 * Taxonomies settings.
	 */
	private function taxonomies_settings() {
		$settings = $this->aio_settings['searchAppearance']['dynamic']['taxonomies'];
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $tax_obj ) {
			if ( empty( $settings[ $taxonomy ] ) ) {
				continue;
			}

			$hash = [
				'title'           => "tax_{$taxonomy}_title",
				'metaDescription' => "tax_{$taxonomy}_description",
			];
			$this->replace( $hash, $settings[ $taxonomy ], $this->titles, 'convert_variables' );

			$robots                                   = $this->get_robots_data( $settings[ $taxonomy ]['advanced']['robotsMeta'] );
			$this->titles[ "tax_{$taxonomy}_robots" ] = $robots['robots'];
			$this->titles[ "tax_{$taxonomy}_advanced_robots" ] = $robots['advanced_robots'];
			$this->titles[ "tax_{$taxonomy}_add_meta_box" ]    = ! empty( $settings[ $taxonomy ]['show'] ) ? 'on' : '';
		}
	}

	/**
	 * Function to get Robots data.
	 *
	 * @param  array $aioseo_robots AIOSEO robots.
	 * @return array Robots data.
	 */
	private function get_robots_data( $aioseo_robots ) {
		$robots          = [];
		$advanced_robots = [];
		$keys            = [
			'maxSnippet'      => 'max-snippet',
			'maxVideoPreview' => 'max-video-preview',
			'maxImagePreview' => 'max-image-preview',
		];
		foreach ( $aioseo_robots as $key => $value ) {
			if ( in_array( $key, [ 'noindex', 'nofollow', 'noarchive', 'noimageindex', 'nosnippet' ], true ) && $value ) {
				$robots[] = $key;
			}

			if ( in_array( $key, [ 'maxSnippet', 'maxVideoPreview', 'maxImagePreview' ], true ) && $value ) {
				$advanced_robots[ $keys[ $key ] ] = $value;
			}
		}
		$robots[] = ! in_array( 'noindex', $robots, true ) ? 'index' : '';

		return [
			'robots'          => $robots,
			'advanced_robots' => $advanced_robots,
		];
	}

	/**
	 * Import post meta of plugin.
	 *
	 * @return array
	 */
	protected function postmeta() {
		$this->set_pagination( $this->get_posts( true ) );
		$posts = $this->get_posts();

		foreach ( $posts as $post ) {
			$post_id = (int) $post->post_id;
			$post    = (array) $post;
			$hash    = [
				'title'                    => 'rank_math_title',
				'description'              => 'rank_math_description',
				'canonical_url'            => 'rank_math_canonical_url',
				'og_title'                 => 'rank_math_facebook_title',
				'og_description'           => 'rank_math_facebook_description',
				'og_image_custom_url'      => 'rank_math_facebook_image',
				'twitter_title'            => 'rank_math_twitter_title',
				'twitter_description'      => 'rank_math_twitter_description',
				'twitter_image_custom_url' => 'rank_math_twitter_image',
			];
			$this->replace_meta( $hash, $post, $post_id, 'post', 'convert_variables' );
			$this->replace_meta( [ 'twitter_use_og' => 'rank_math_twitter_use_facebook' ], $post, $post_id, 'post', 'convert_bool' );
			$this->set_object_robots( $post_id, $post, 'post' );
			$this->set_keywords( $post_id, $post, 'post' );
			$this->add_schema_data( $post_id, $post );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import term meta of plugin.
	 *
	 * @return array
	 */
	protected function termmeta() {
		$this->set_pagination( $this->get_terms( true ) );
		$terms = $this->get_terms();

		foreach ( $terms as $term ) {
			$term_id = $term->term_id;
			$term    = (array) $term;
			$hash    = [
				'title'                    => 'rank_math_title',
				'description'              => 'rank_math_description',
				'canonical_url'            => 'rank_math_canonical_url',
				'og_title'                 => 'rank_math_facebook_title',
				'og_description'           => 'rank_math_facebook_description',
				'og_image_custom_url'      => 'rank_math_facebook_image',
				'twitter_title'            => 'rank_math_twitter_title',
				'twitter_description'      => 'rank_math_twitter_description',
				'twitter_image_custom_url' => 'rank_math_twitter_image',
			];
			$this->replace_meta( $hash, $term, $term_id, 'term', 'convert_variables' );
			$this->replace_meta( [ 'twitter_use_og' => 'rank_math_twitter_use_facebook' ], $term, $term_id, 'term', 'convert_bool' );
			$this->set_object_robots( $term_id, $term, 'term' );
			$this->set_keywords( $term_id, $term, 'term' );
		}

		return [ 'count' => count( $terms ) ];
	}

	/**
	 * Deactivate plugin action.
	 */
	protected function deactivate() {
		if ( is_plugin_active( $this->get_plugin_file() ) ) {
			deactivate_plugins( $this->get_plugin_file() );
			deactivate_plugins( 'aioseo-image-seo/aioseo-image-seo.php' );
			deactivate_plugins( 'aioseo-local-business/aioseo-local-business.php' );
			deactivate_plugins( 'aioseo-news-sitemap/aioseo-news-sitemap.php' );
			deactivate_plugins( 'aioseo-video-sitemap/aioseo-video-sitemap.php' );
			deactivate_plugins( 'aioseo-local-business/aioseo-local-business.php' );
			deactivate_plugins( 'aioseo-redirects/aioseo-redirects.php' );
		}

		return true;
	}

	/**
	 * Get all post IDs of all allowed post types only.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	private function get_posts( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'aioseo_posts' );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select( '*' )->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Get all post IDs of all allowed post types only.
	 *
	 * @param bool $count If we need count only for pagination purposes.
	 * @return int|array
	 */
	private function get_terms( $count = false ) {
		$paged = $this->get_pagination_arg( 'page' );
		$table = DB::query_builder( 'aioseo_terms' );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select( '*' )->page( $paged - 1, $this->items_per_page )->get();
	}

	/**
	 * Import Schema Data.
	 *
	 * @param int   $post_id Post ID.
	 * @param array $post    Post data.
	 */
	private function add_schema_data( $post_id, $post ) {
		if ( empty( $post['schema_type'] ) || ! in_array( $post['schema_type'], [ 'SoftwareApplication', 'Product', 'Recipe', 'Course' ], true ) ) {
			return;
		}

		$type   = strtolower( $post['schema_type'] );
		$type   = 'softwareapplication' === $type ? 'software' : $type;
		$schema = json_decode( $post['schema_type_options'], true );
		$data   = $this->$type( $schema[ $type ] );
		$schema = [
			'metadata' => [
				'title'                   => $data['@type'],
				'isPrimary'               => true,
				'type'                    => 'template',
				'reviewLocation'          => 'custom',
				'reviewLocationShortcode' => '[rank_math_rich_snippet]',
			],
		] + $data;

		update_post_meta( $post_id, 'rank_math_schema_' . $schema['@type'], $schema );
	}

	/**
	 * Import Software Schema Data.
	 *
	 * @param array $data Software schema data.
	 */
	private function software( $data ) {
		$schema = [
			'@type'               => 'SoftwareApplication',
			'name'                => ! empty( $data['name'] ) ? $data['name'] : '%seo_title%',
			'applicationCategory' => $data['category'],
			'offers'              => [
				'@type'         => 'Offer',
				'price'         => $data['price'],
				'priceCurrency' => $data['currency'],
			],
		];

		if ( ! empty( $data['operatingSystems'] ) ) {
			$operating_system = array_map(
				function( $system ) {
					return $system['value'];
				},
				json_decode( $data['operatingSystems'], true )
			);

			$schema['operatingSystem'] = implode( ', ', $operating_system );
		}

		if ( ! empty( $data['reviews'] ) ) {
			$reviews          = json_decode( $data['reviews'][0], true );
			$schema['review'] = [
				'@type'         => 'Review',
				'datePublished' => '%date(Y-m-dTH:i:sP)%',
				'dateModified'  => '%modified(Y-m-dTH:i:sP)%',
				'author'        => [
					'@type' => 'Person',
					'name'  => '%name%',
				],
				'reviewRating'  => [
					'@type'       => 'Rating',
					'ratingValue' => ! empty( $reviews['rating'] ) ? $reviews['rating'] : '',
				],
			];
		}

		return $schema;
	}

	/**
	 * Import Product Schema Data.
	 *
	 * @param array $data Product schema data.
	 */
	private function product( $data ) {
		return [
			'@type'       => 'Product',
			'name'        => ! empty( $data['name'] ) ? $data['name'] : '%seo_title%',
			'description' => ! empty( $data['description'] ) ? $data['description'] : '%seo_description%',
			'gtin8'       => $data['identifier'],
			'brand'       => [
				'@type' => 'Brand',
				'name'  => $data['brand'],
			],
			'offers'      => [
				'@type'           => 'Offer',
				'price'           => $data['price'],
				'priceCurrency'   => $data['currency'],
				'priceValidUntil' => $data['priceValidUntil'],
				'availability'    => str_replace( 'https://schema.org/', '', $data['availability'] ),
			],
		];
	}

	/**
	 * Import Course Schema Data.
	 *
	 * @param array $data Course schema data.
	 */
	private function course( $data ) {
		return [
			'@type'       => 'Course',
			'name'        => $data['name'],
			'description' => $data['description'],
			'provider'    => [
				'@type' => 'Organization',
				'name'  => $data['provider'],
			],
		];
	}

	/**
	 * Import Recipe Schema Data.
	 *
	 * @param array $data Recipe schema data.
	 */
	private function recipe( $data ) {
		$schema = [
			'@type'          => 'Recipe',
			'name'           => $data['name'],
			'recipeCategory' => $data['dishType'],
			'recipeCuisine'  => $data['cuisineType'],
			'prepTime'       => "PT{$data['preparationTime']}M",
			'cookTime'       => "PT{$data['cookingTime']}M",
			'recipeYield'    => "PT{$data['servings']}M",
			'nutrition'      => [
				'@type'    => 'NutritionInformation',
				'calories' => $data['calories'],
			],
		];

		if ( ! empty( $data['keywords'] ) ) {
			$keywords = array_map(
				function( $keyword ) {
					return $keyword['value'];
				},
				json_decode( $data['keywords'], true )
			);

			$schema['keywords'] = implode( ', ', $keywords );
		}

		if ( ! empty( $data['ingredients'] ) ) {
			$schema['recipeIngredient'] = array_map(
				function( $ingredient ) {
					return $ingredient['value'];
				},
				json_decode( $data['ingredients'], true )
			);
		}

		if ( ! empty( $data['instructions'] ) ) {
			$schema['recipeInstructions'] = [
				'@type'           => 'HowToSection',
				'itemListElement' => [],
			];

			foreach ( $data['instructions'] as $instruction ) {
				$instruction_data = json_decode( $instruction, true );

				$schema['recipeInstructions']['itemListElement'][] = [
					'@type' => 'HowtoStep',
					'text'  => $instruction_data['content'],
				];
			}
		}

		return $schema;
	}

	/**
	 * Set Keywords.
	 *
	 * @param int    $object_id   Object ID.
	 * @param array  $object      Object data.
	 * @param string $object_type Current Object type.
	 */
	private function set_keywords( $object_id, $object, $object_type ) {
		$keywords   = [];
		$keyphrases = json_decode( $object['keyphrases'], true );

		if ( ! empty( $keyphrases['focus']['keyphrase'] ) ) {
			$keywords[] = $keyphrases['focus']['keyphrase'];
		}

		if ( ! empty( $keyphrases['additional'] ) ) {
			foreach ( $keyphrases['additional'] as $keyword ) {
				$keywords[] = $keyword['keyphrase'];
			}
		}

		if ( empty( $keywords ) ) {
			return;
		}

		$this->update_meta( $object_type, $object_id, 'rank_math_focus_keyword', implode( ',', $keywords ) );
	}

	/**
	 * Set object robots meta.
	 *
	 * @param int    $object_id   Object ID.
	 * @param array  $object      Object data.
	 * @param string $object_type Current Object type.
	 */
	private function set_object_robots( $object_id, $object, $object_type ) {
		// Early bail if robots data is set in Rank Math plugin.
		if ( ! empty( $this->get_meta( $object_type, $object_id, 'rank_math_robots' ) ) || ! empty( $object['robots_default'] ) ) {
			return;
		}

		// ROBOTS.
		$robots = [];
		foreach ( [ 'robots_noindex', 'robots_noarchive', 'robots_nosnippet', 'robots_nofollow', 'robots_noimageindex' ] as $key ) {
			if ( empty( $object[ $key ] ) ) {
				continue;
			}

			$robots[] = str_replace( 'robots_', '', $key );
		}

		if ( ! in_array( 'noindex', $robots, true ) ) {
			$robots[] = 'index';
		}
		$this->update_meta( $object_type, $object_id, 'rank_math_robots', array_unique( $robots ) );

		$advanced_robots = [];
		$keys            = [
			'robots_max_snippet'      => 'max-snippet',
			'robots_max_videopreview' => 'max-video-preview',
			'robots_max_imagepreview' => 'max-image-preview',
		];
		foreach ( [ 'robots_max_snippet', 'robots_max_videopreview', 'robots_max_imagepreview' ] as $key ) {
			if ( empty( $object[ $key ] ) ) {
				continue;
			}

			$advanced_robots[ $keys[ $key ] ] = $object[ $key ];
		}

		$this->update_meta( $object_type, $object_id, 'rank_math_advanced_robots', array_unique( $advanced_robots ) );
	}

	/**
	 * Replace meta based on key/value hash.
	 *
	 * @param array  $hash        Array of hash for search and replace.
	 * @param array  $source      Array for source where to search.
	 * @param int    $object_id   Object id for destination where to save.
	 * @param string $object_type Object type for destination where to save.
	 * @param bool   $convert     (Optional) Conversion type. Default: false.
	 */
	protected function replace_meta( $hash, $source, $object_id, $object_type, $convert = false ) {
		foreach ( $hash as $search => $replace ) {
			$value = isset( $source[ $search ] ) ? $source[ $search ] : $this->get_meta( $object_type, $object_id, $search );
			if ( ! isset( $value ) ) {
				continue;
			}

			$this->update_meta(
				$object_type,
				$object_id,
				$replace,
				false !== $convert ? $this->$convert( $value ) : $value
			);
		}
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

			$post_terms = wp_get_object_terms( $location->ID, 'aioseo-location-category', [ 'fields' => 'slugs' ] );
			if ( ! empty( $post_terms ) && ! is_wp_error( $post_terms ) ) {
				wp_set_object_terms( $post_id, $post_terms, 'rank_math_location_category', false );
			}

			$this->locations_meta( $location->ID, $post_id );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import Locations terms.
	 */
	private function import_locations_terms() {
		$terms = get_terms( 'aioseo-location-category' );
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
		$metas = DB::query_builder( 'aioseo_posts' )->where( 'post_id', $old_post_id )->select()->one();
		if ( empty( $metas->local_seo ) ) {
			return;
		}

		$locations_data = json_decode( $metas->local_seo, true );
		$locations_meta = ! empty( $locations_data['locations'] ) ? $locations_data['locations']['business'] : [];
		$opening_hours  = ! empty( $locations_data['openingHours'] ) ? $locations_data['openingHours'] : [];

		$schema = [
			'metadata'   => [
				'type'           => 'template',
				'shortcode'      => uniqid( 's-' ),
				'isPrimary'      => true,
				'title'          => 'Local Business',
				'use_24h_format' => ! empty( $opening_hours['use24hFormat'] ),
				'open247'        => ! empty( $opening_hours['alwaysOpen'] ),
				'timeZone'       => ! empty( $opening_hours['timezone'] ),
			],
			'@type'      => $locations_meta['businessType'],
			'name'       => ! empty( $locations_meta['name'] ) ? $locations_meta['name'] : '%seo_title%',
			'url'        => ! empty( $locations_meta['urls'] ) ? $locations_meta['urls']['website'] : '%url%',
			'address'    => ! empty( $locations_meta['address'] ) ? $this->replace_address( $locations_meta['address'] ) : '',
			'areaServed' => ! empty( $locations_meta['areaServed'] ) ? $locations_meta['areaServed'] : '',
		];

		if ( ! empty( $locations_meta['contact'] ) ) {
			$contact             = $locations_meta['contact'];
			$schema['email']     = ! empty( $contact['email'] ) ? $contact['email'] : '';
			$schema['telephone'] = ! empty( $contact['phone'] ) ? $contact['phone'] : '';
			$schema['faxNumber'] = ! empty( $contact['fax'] ) ? $contact['fax'] : '';
		}

		if ( ! empty( $locations_meta['ids'] ) ) {
			$ids             = $locations_meta['ids'];
			$schema['vatID'] = ! empty( $ids['vat'] ) ? $ids['vat'] : '';
			$schema['taxID'] = ! empty( $ids['tax'] ) ? $ids['tax'] : '';
		}

		if ( ! empty( $locations_meta['payment'] ) ) {
			$payment                   = $locations_meta['payment'];
			$schema['priceRange']      = ! empty( $payment['priceRange'] ) ? $payment['priceRange'] : '';
			$schema['paymentAccepted'] = ! empty( $payment['methods'] ) ? $payment['methods'] : '';

			if ( ! empty( $payment['currenciesAccepted'] ) ) {
				$schema['currenciesAccepted'] = implode(
					', ',
					array_map(
						function( $value ) {
							return $value['value'];
						},
						json_decode( $payment['currenciesAccepted'], true )
					)
				);
			}
		}

		if ( ! empty( $locations_meta['image'] ) ) {
			$schema['logo'] = [
				'@type' => 'ImageObject',
				'url'   => $locations_meta['image'],
			];
		}

		$schema['openingHoursSpecification'] = $this->replace_opening_hours( $opening_hours['days'] );

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
		$days = [ 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday' ];
		foreach ( $days as $day ) {
			if ( ! empty( $opening_hours[ $day ]['closed'] ) ) {
				continue;
			}

			if ( ! empty( $opening_hours[ $day ]['open24h'] ) ) {
				$opens  = '00:00';
				$closes = '23:59';
			} else {
				$opens  = ! empty( $opening_hours[ $day ]['openTime'] ) ? $opening_hours[ $day ]['openTime'] : '';
				$closes = ! empty( $opening_hours[ $day ]['closeTime'] ) ? $opening_hours[ $day ]['closeTime'] : '';
			}

			if ( ! $opens ) {
				continue;
			}

			$data[ $day ] = [
				'@type'     => 'OpeningHoursSpecification',
				'dayOfWeek' => ucfirst( $day ),
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
			'streetLine1' => 'streetAddress',
			'streetLine2' => 'addressLocality',
			'state'       => 'addressRegion',
			'zipCode'     => 'postalCode',
			'country'     => 'addressCountry',
		];

		foreach ( $hash as $key => $value ) {
			$data[ $value ] = isset( $address[ $key ] ) ? $address[ $key ] : '';
		}

		if ( ! empty( $address['city'] ) ) {
			$data['addressLocality'] = $data['addressLocality'] . ', ' . $address['city'];
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
		$table = DB::query_builder( 'posts' )->where( 'post_type', 'aioseo-location' );

		return $count ? absint( $table->selectCount( 'ID', 'total' )->getVar() ) :
			$table->select()->page( $paged - 1, $this->items_per_page )->get();
	}
}
