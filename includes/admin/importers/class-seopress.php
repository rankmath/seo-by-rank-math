<?php
/**
 * The SEOPress Import Class
 *
 * @since      1.0.24
 * @package    RankMath
 * @subpackage RankMath\Admin\Importers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Importers;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Redirections\Redirection;
use RankMath\Schema\JsonLD;
use RankMath\Schema\Singular;
use MyThemeShop\Helpers\DB;

defined( 'ABSPATH' ) || exit;

/**
 * SEOPress class.
 */
class SEOPress extends Plugin_Importer {

	/**
	 * The plugin name.
	 *
	 * @var string
	 */
	protected $plugin_name = 'SEOPress';

	/**
	 * Plugin options meta key.
	 *
	 * @var string
	 */
	protected $meta_key = 'seopress';

	/**
	 * Option keys to import and clean.
	 *
	 * @var array
	 */
	protected $option_keys = [ 'seopress', 'seopress_%' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'settings', 'postmeta', 'termmeta', 'redirections' ];

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
		$string = str_replace( '%%sitetitle%%', '%sitename%', $string );
		$string = str_replace( '%%tagline%%', '%sitedesc%', $string );
		$string = str_replace( '%%post_title%%', '%title%', $string );
		$string = str_replace( '%%post_excerpt%%', '%excerpt%', $string );
		$string = str_replace( '%%post_date%%', '%date%', $string );
		$string = str_replace( '%%post_modified_date%%', '%modified%', $string );
		$string = str_replace( '%post_author%%', '%name%', $string );
		$string = str_replace( '%%post_category%%', '%category%', $string );
		$string = str_replace( '%%post_tag%%', '%tag%', $string );
		$string = str_replace( '%%_category_title%%', '%term%', $string );
		$string = str_replace( '%%_category_description%%', '%term_description%', $string );
		$string = str_replace( '%%tag_title%%', '%term%', $string );
		$string = str_replace( '%%tag_description%%', '%term_description%', $string );
		$string = str_replace( '%%term_title%%', '%term%', $string );
		$string = str_replace( '%%term_description%%', '%term_description%', $string );
		$string = str_replace( '%%search_keywords%%', '%search_query%', $string );
		$string = str_replace( '%%current_pagination%%', '%page%', $string );
		$string = str_replace( '%%cpt_plural%%', '%pt_plural%', $string );
		$string = str_replace( '%%archive_title%%', '%title%', $string );
		$string = str_replace( '%%archive_date%%', '%currentdate%', $string );
		$string = str_replace( '%%archive_date_day%%', '%currentday%', $string );
		$string = str_replace( '%%archive_date_month%%', '%currentmonth%', $string );
		$string = str_replace( '%%archive_date_year%%', '%year%', $string );
		$string = str_replace( '%%currentdate%%', '%currentdate%', $string );
		$string = str_replace( '%%currentday%%', '%currentday%', $string );
		$string = str_replace( '%%currentmonth%%', '%currentmonth%', $string );
		$string = str_replace( '%%currentyear%%', '%currentyear%', $string );
		$string = str_replace( '%%currenttime%%', '%time%', $string );
		$string = str_replace( '%%author_bio%%', '%user_description%', $string );
		$string = str_replace( '%%wc_single_cat%%', '%term%', $string );
		$string = str_replace( '%%wc_single_tag%%', '%term%', $string );
		$string = str_replace( '%%wc_single_short_desc%%', '%wc_shortdesc%', $string );
		$string = str_replace( '%%wc_single_price%%', '%wc_price%', $string );

		return str_replace( '%%', '%', $string );
	}

	/**
	 * Deactivate plugin action.
	 */
	protected function deactivate() {
		if ( is_plugin_active( $this->get_plugin_file() ) ) {
			deactivate_plugins( $this->get_plugin_file() );
			deactivate_plugins( 'wp-seopress-pro/seopress-pro.php' );
		}

		return true;
	}

	/**
	 * Import settings of plugin.
	 *
	 * @return bool
	 */
	protected function settings() {
		$this->get_settings();

		$seopress_titles  = get_option( 'seopress_titles_option_name' );
		$seopress_sitemap = get_option( 'seopress_xml_sitemap_option_name' );
		$seopress_local   = get_option( 'seopress_pro_option_name' );

		// Titles & Descriptions.
		$hash = [
			'seopress_titles_archives_author_disable' => 'disable_author_archives',
			'seopress_titles_archives_date_disable'   => 'disable_date_archives',
			'seopress_titles_home_site_title'         => 'homepage_title',
			'seopress_titles_home_site_desc'          => 'homepage_description',
			'seopress_titles_archives_author_title'   => 'author_archive_title',
			'seopress_titles_archives_author_desc'    => 'author_archive_description',
			'seopress_titles_archives_date_title'     => 'date_archive_title',
			'seopress_titles_archives_date_desc'      => 'date_archive_description',
			'seopress_titles_archives_search_title'   => 'search_title',
			'seopress_titles_archives_404_title'      => '404_title',
		];

		$this->replace( $hash, $seopress_titles, $this->titles, 'convert_variables' );
		$this->replace( $hash, $seopress_titles, $this->titles, 'convert_bool' );
		$this->titles['title_separator'] = \RankMath\CMB2::sanitize_htmlentities( $seopress_titles['seopress_titles_sep'] );

		$this->titles['date_archive_robots'] = ! empty( $seopress_titles['seopress_titles_archives_date_noindex'] ) ? [ 'noindex' ] : [];
		$this->set_robots( 'author', ! empty( $seopress_titles['seopress_titles_archives_author_noindex'] ), '' );

		$this->update_modules( $seopress_local, $seopress_sitemap );
		$this->social_settings();
		$this->advanced_settings();
		$this->post_type_settings( $seopress_titles, $seopress_sitemap );
		$this->taxonomies_settings( $seopress_titles, $seopress_sitemap );
		$this->local_seo_settings( $seopress_local );
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

		$post_ids = $this->get_post_ids();

		$this->set_primary_term( $post_ids );

		$hash = [
			'_seopress_titles_title'         => 'rank_math_title',
			'_seopress_titles_desc'          => 'rank_math_description',
			'_seopress_analysis_target_kw'   => 'rank_math_focus_keyword',
			'_seopress_robots_canonical'     => 'rank_math_canonical_url',
			'_seopress_social_fb_title'      => 'rank_math_facebook_title',
			'_seopress_social_fb_desc'       => 'rank_math_facebook_description',
			'_seopress_social_fb_img'        => 'rank_math_facebook_image',
			'_seopress_social_twitter_title' => 'rank_math_twitter_title',
			'_seopress_social_twitter_desc'  => 'rank_math_twitter_description',
			'_seopress_social_twitter_img'   => 'rank_math_twitter_image',
			'_seopress_robots_breadcrumbs'   => 'rank_math_breadcrumb_title',
		];

		// Set Converter.
		$this->json_ld = new JsonLD();
		$this->single  = new Singular();

		foreach ( $post_ids as $post ) {
			$post_id = $post->ID;
			$this->replace_meta( $hash, null, $post_id, 'post', 'convert_variables' );
			delete_post_meta( $post_id, 'rank_math_permalink' );

			$this->replace_image( get_post_meta( $post_id, '_seopress_social_fb_img', true ), 'post', 'rank_math_facebook_image', 'rank_math_facebook_image_id', $post_id );
			$this->replace_image( get_post_meta( $post_id, '_seopress_social_twitter_img', true ), 'post', 'rank_math_twitter_image', 'rank_math_twitter_image_id', $post_id );

			$this->is_twitter_using_facebook( 'post', $post_id );
			$this->set_object_robots( $post_id, 'post' );
			$this->set_schema_data( $post_id );
			$this->set_object_redirection( $post_id, 'post' );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import term meta of plugin.
	 *
	 * @return array
	 */
	protected function termmeta() {
		$count = 0;
		$terms = new \WP_Term_Query(
			[
				'meta_key'   => '_seopress_titles_title',
				'fields'     => 'ids',
				'hide_empty' => false,
				'get'        => 'all',
			]
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		$hash = [
			'_seopress_titles_title'         => 'rank_math_title',
			'_seopress_titles_desc'          => 'rank_math_description',
			'_seopress_robots_canonical'     => 'rank_math_canonical_url',
			'_seopress_social_fb_title'      => 'rank_math_facebook_title',
			'_seopress_social_fb_desc'       => 'rank_math_facebook_description',
			'_seopress_social_fb_img'        => 'rank_math_facebook_image',
			'_seopress_social_twitter_title' => 'rank_math_twitter_title',
			'_seopress_social_twitter_desc'  => 'rank_math_twitter_description',
			'_seopress_social_twitter_img'   => 'rank_math_twitter_image',
		];

		foreach ( $terms->get_terms() as $term_id ) {
			$count++;

			$this->replace_meta( $hash, [], $term_id, 'term', 'convert_variables' );
			delete_term_meta( $term_id, 'rank_math_permalink' );
			$this->is_twitter_using_facebook( 'term', $term_id );
			$this->set_object_robots( $term_id, 'term' );
			$this->set_object_redirection( $term_id, 'term' );
		}

		return compact( 'count' );
	}

	/**
	 * Imports redirections data.
	 *
	 * @return array
	 */
	protected function redirections() {
		$redirections = get_posts(
			[
				'posts_per_page' => -1,
				'post_type'      => 'seopress_404',
			]
		);

		if ( empty( $redirections ) ) {
			return false;
		}

		$count = 0;
		foreach ( $redirections as $redirection ) {

			$data = [
				'source'      => $redirection->post_title,
				'destination' => get_post_meta( $redirection->ID, '_seopress_redirections_value', true ),
				'code'        => get_post_meta( $redirection->ID, '_seopress_redirections_type', true ),
				'status'      => 'publish' === $redirection->post_status ? true : false,
			];
			if ( false !== $this->save_redirection( $data ) ) {
				$count++;
			}
		}

		return compact( 'count' );
	}

	/**
	 * Update Modules.
	 *
	 * @param array $seopress_local   Local SEO Settings.
	 * @param array $seopress_sitemap Sitemap Settings.
	 */
	private function update_modules( $seopress_local, $seopress_sitemap ) {
		$seopress_toggle = get_option( 'seopress_toggle' );

		// Enable/Disable Modules.
		$modules = [
			'local-seo'    => ! empty( $seopress_toggle['toggle-local-business'] ) ? 'on' : 'off',
			'sitemap'      => ! empty( $seopress_toggle['toggle-xml-sitemap'] ) && ! empty( $seopress_sitemap['seopress_xml_sitemap_general_enable'] ) ? 'on' : 'off',
			'rich-snippet' => ! empty( $seopress_toggle['toggle-rich-snippets'] ) ? 'on' : 'off',
			'404-monitor'  => ! empty( $seopress_toggle['toggle-404'] ) && ! empty( $seopress_local['seopress_404_enable'] ) ? 'on' : 'off',
		];
		foreach ( $modules as $key => $value ) {
			Helper::update_modules( [ $key => $value ] );
		}
	}

	/**
	 * Save redirection.
	 *
	 * @param WP_Post $redirection Redirection object.
	 *
	 * @return mixed
	 */
	private function save_redirection( $redirection ) {
		if ( empty( $redirection['source'] ) || empty( $redirection['destination'] ) ) {
			return false;
		}

		$item = Redirection::from(
			[
				'sources'     => [
					[
						'pattern'    => $redirection['source'],
						'comparison' => 'exact',
					],
				],
				'url_to'      => $redirection['destination'],
				'header_code' => $redirection['code'],
				'status'      => $redirection['status'] ? 'active' : 'inactive',
			]
		);

		return $item->save();
	}

	/**
	 * Social settings.
	 */
	private function social_settings() {
		$social = get_option( 'seopress_social_option_name' );
		$hash   = [
			'seopress_social_accounts_facebook'          => 'social_url_facebook',
			'seopress_social_facebook_link_ownership_id' => 'facebook_author_urls',
			'seopress_social_facebook_img'               => 'open_graph_image',
			'seopress_social_facebook_admin_id'          => 'facebook_admin_id',
			'seopress_social_facebook_app_id'            => 'facebook_app_id',
			'seopress_social_accounts_twitter'           => 'twitter_author_names',
			'seopress_social_knowledge_name'             => 'knowledgegraph_name',
			'seopress_social_knowledge_img'              => 'knowledgegraph_logo',
		];
		$this->replace( $hash, $social, $this->titles );

		// OpenGraph.
		if ( isset( $social['og_default_image'] ) ) {
			$this->replace_image( $social['og_default_image'], $this->titles, 'open_graph_image', 'open_graph_image_id' );
		}

		if ( isset( $social['og_frontpage_image'] ) ) {
			$this->replace_image( $social['og_frontpage_image'], $this->titles, 'homepage_facebook_image', 'homepage_facebook_image_id' );
		}

		// Phone Numbers.
		if ( ! empty( $social['seopress_social_knowledge_phone'] ) ) {
			$this->titles['phone_numbers'] = [
				[
					'type'   => $social['seopress_social_knowledge_contact_type'],
					'number' => $social['seopress_social_knowledge_phone'],
				],
			];
		}
		$this->titles['knowledgegraph_type'] = isset( $social['seopress_social_knowledge_type'] ) && 'organization' === strtolower( $social['seopress_social_knowledge_type'] ) ? 'company' : 'person';
	}

	/**
	 * Post type settings.
	 *
	 * @param array $seopress_titles  Titles & Meta Settings.
	 * @param array $seopress_sitemap Sitemap Settings.
	 */
	private function post_type_settings( $seopress_titles, $seopress_sitemap ) {
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$this->titles[ "pt_{$post_type}_title" ]       = isset( $seopress_titles['seopress_titles_single_titles'][ $post_type ] ) ? $this->convert_variables( $seopress_titles['seopress_titles_single_titles'][ $post_type ]['title'] ) : '';
			$this->titles[ "pt_{$post_type}_description" ] = isset( $seopress_titles['seopress_titles_single_titles'][ $post_type ] ) ? $this->convert_variables( $seopress_titles['seopress_titles_single_titles'][ $post_type ]['description'] ) : '';

			$this->set_robots(
				"pt_{$post_type}",
				! empty( $seopress_titles['seopress_titles_single_titles'][ $post_type ]['noindex'] ),
				! empty( $seopress_titles['seopress_titles_single_titles'][ $post_type ]['nofollow'] )
			);

			$enable_sitemap                             = $this->enable_sitemap( 'post_types', $post_type, $seopress_sitemap );
			$this->sitemap[ "pt_{$post_type}_sitemap" ] = $enable_sitemap ? 'on' : 'off';

			if ( 'attachment' === $post_type && $enable_sitemap ) {
				$this->settings['attachment_redirect_urls'] = 'off';
			}
		}
	}

	/**
	 * Taxonomies settings.
	 *
	 * @param array $seopress_titles Titles & Meta Settings.
	 * @param array $seopress_sitemap Sitemap Settings.
	 */
	private function taxonomies_settings( $seopress_titles, $seopress_sitemap ) {
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$this->titles[ "tax_{$taxonomy}_title" ]       = ! empty( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['title'] ) ? $this->convert_variables( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['title'] ) : '';
			$this->titles[ "tax_{$taxonomy}_description" ] = ! empty( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['description'] ) ? $this->convert_variables( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['description'] ) : '';

			$this->set_robots(
				"tax_{$taxonomy}",
				! empty( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['noindex'] ),
				! empty( $seopress_titles['seopress_titles_tax_titles'][ $taxonomy ]['nofollow'] )
			);

			$this->sitemap[ "tax_{$taxonomy}_sitemap" ] = $this->enable_sitemap( 'taxonomies', $taxonomy, $seopress_sitemap ) ? 'on' : 'off';
		}
	}

	/**
	 * Whether to enable sitemap.
	 *
	 * @param string $object_prefix    post_types/taxonomies.
	 * @param string $object_type      Current object type.
	 * @param string $seopress_sitemap Sitemap settings.
	 *
	 * @return bool
	 */
	private function enable_sitemap( $object_prefix, $object_type, $seopress_sitemap ) {
		return ! empty( $seopress_sitemap[ "seopress_xml_sitemap_{$object_prefix}_list" ][ $object_type ]['include'] );
	}

	/**
	 * Set robots settings.
	 *
	 * @param string $prefix   Setting key prefix.
	 * @param bool   $noindex  Is noindex.
	 * @param bool   $nofollow Is nofollow.
	 */
	private function set_robots( $prefix, $noindex, $nofollow ) {
		if ( $noindex || $nofollow ) {
			$robots = "{$prefix}_robots";
			$custom = "{$prefix}_custom_robots";

			// Settings.
			$this->titles[ $custom ]   = 'on';
			$this->titles[ $robots ][] = $noindex ? 'noindex' : '';
			$this->titles[ $robots ][] = $nofollow ? 'nofollow' : '';
			$this->titles[ $robots ]   = array_unique( $this->titles[ $robots ] );
		}
	}

	/**
	 * Set Advanced settings.
	 */
	private function advanced_settings() {
		$seopress_advanced = get_option( 'seopress_advanced_option_name' );

		$hash = [
			'seopress_advanced_advanced_google'    => 'google_verify',
			'seopress_advanced_advanced_bing'      => 'bing_verify',
			'seopress_advanced_advanced_yandex'    => 'yandex_verify',
			'seopress_advanced_advanced_pinterest' => 'pinterest_verify',
		];
		$this->replace( $hash, $seopress_advanced, $this->settings );
		$this->replace( $hash, $seopress_advanced, $this->settings, 'convert_bool' );

		$this->settings['attachment_redirect_urls'] = ! empty( $seopress_advanced['seopress_advanced_advanced_attachments'] ) ? 'on' : 'off';
		$this->settings['strip_category_base']      = ! empty( $seopress_advanced['seopress_advanced_advanced_category_url'] ) ? 'on' : 'off';

		$set_alt   = ! empty( $seopress_advanced['seopress_advanced_advanced_image_auto_alt_editor'] ) ? 'on' : 'off';
		$set_title = ! empty( $seopress_advanced['seopress_advanced_advanced_image_auto_title_editor'] ) ? 'on' : 'off';

		$this->settings['add_img_alt']      = $set_alt;
		$this->settings['add_img_title']    = $set_title;
		$this->settings['img_alt_format']   = 'on' === $set_alt ? ' %filename%' : '';
		$this->settings['img_title_format'] = 'on' === $set_title ? ' %filename%' : '';
	}

	/**
	 * Local SEO settings.
	 *
	 * @param array $seopress_local Local SEOPress data.
	 */
	private function local_seo_settings( $seopress_local ) {
		if ( empty( $seopress_local ) ) {
			return;
		}

		// Breadcrumbs.
		$hash = [
			'seopress_breadcrumbs_i18n_home'   => 'breadcrumbs_home_label',
			'seopress_breadcrumbs_i18n_search' => 'breadcrumbs_search_format',
			'seopress_breadcrumbs_i18n_404'    => 'breadcrumbs_404_label',
			'seopress_breadcrumbs_enable'      => 'breadcrumbs',
		];
		$this->replace( $hash, $seopress_local, $this->settings );
		$this->replace( $hash, $seopress_local, $this->settings, 'convert_bool' );
		$this->settings['breadcrumbs_separator'] = \RankMath\CMB2::sanitize_htmlentities( $seopress_local['seopress_breadcrumbs_separator'] );

		$hash = [
			'seopress_local_business_type'        => 'local_business_type',
			'seopress_local_business_price_range' => 'price_range',
			'seopress_local_business_url'         => 'url',
		];
		$this->replace( $hash, $seopress_local, $this->titles, 'convert_variables' );

		// Street Address.
		$address = [];
		$hash    = [
			'seopress_local_business_street_address'   => 'streetAddress',
			'seopress_local_business_address_locality' => 'addressLocality',
			'seopress_local_business_address_region'   => 'addressRegion',
			'seopress_local_business_postal_code'      => 'postalCode',
			'seopress_local_business_address_country'  => 'addressCountry',
		];
		$this->replace( $hash, $seopress_local, $address );
		$this->titles['local_address'] = $address;

		// Coordinates.
		if ( ! empty( $seopress_local['seopress_local_business_lat'] ) && ! empty( $seopress_local['seopress_local_business_lon'] ) ) {
			$this->titles['geo'] = $seopress_local['seopress_local_business_lat'] . ', ' . $seopress_local['seopress_local_business_lon'];
		}

		$this->seopress_pro_settings( $seopress_local );
		$this->seopress_set_opening_hours( $seopress_local );
	}

	/**
	 * 404 settings.
	 *
	 * @param array $seopress_local SEOPress Pro Settings.
	 */
	private function seopress_pro_settings( $seopress_local ) {
		Helper::update_modules( [ 'redirections' => 'on' ] );
		$hash = [
			'seopress_rss_before_html'          => 'rss_before_content',
			'seopress_rss_after_html'           => 'rss_after_content',
			'seopress_404_redirect_custom_url'  => 'redirections_custom_url',
			'seopress_404_redirect_status_code' => 'redirections_header_code',
		];
		$this->replace( $hash, $seopress_local, $this->settings );
		$this->settings['redirections_fallback'] = 'none' === $seopress_local['seopress_404_redirect_home'] ? 'default' : ( 'home' === $seopress_local['seopress_404_redirect_home'] ? 'homepage' : 'custom' );
	}

	/**
	 * Set Opening Hours.
	 *
	 * @param array $seopress_local SEOPress Pro Settings.
	 */
	private function seopress_set_opening_hours( $seopress_local ) {
		$hash = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
		$data = [];
		foreach ( $seopress_local['seopress_local_business_opening_hours'] as $key => $opening_hour ) {
			if ( isset( $opening_hour['open'] ) ) {
				continue;
			}
			$data[] = [
				'day'  => $hash[ $key ],
				'time' => $opening_hour['start']['hours'] . ':' . $opening_hour['start']['mins'] . '-' . $opening_hour['end']['hours'] . ':' . $opening_hour['end']['mins'],
			];
		}
		$this->titles['opening_hours'] = $data;
	}


	/**
	 * Set schema data.
	 *
	 * @param int $post_id Post ID.
	 */
	private function set_schema_data( $post_id ) {
		if ( ! $type = get_post_meta( $post_id, '_seopress_pro_rich_snippets_type', true ) ) { // phpcs:ignore
			return;
		}

		if ( $meta_keys = $this->get_schema_metakeys( $type ) ) { // phpcs:ignore
			$schema_type = 's' === substr( $type, -1 ) ? substr( $type, 0, -1 ) : $type;
			update_post_meta( $post_id, 'rank_math_rich_snippet', $schema_type );

			foreach ( $meta_keys as $meta_key => $data ) {
				$value = $this->get_snippet_value( $post_id, $meta_key );
				if ( $value && 'events_location_address' === $meta_key ) {
					$value = [ 'streetAddress' => $value ];
				}

				update_post_meta( $post_id, "rank_math_snippet_{$data}", $value );
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
	}

	/**
	 * Set object redirection.
	 *
	 * @param int    $object_id   Object id for destination.
	 * @param string $object_type Object type for destination.
	 */
	private function set_object_redirection( $object_id, $object_type ) {
		$source_url = 'term' === $object_type ? get_term_link( $object_id ) : get_permalink( $object_id );
		if ( is_wp_error( $source_url ) ) { // phpcs:ignore
			return;
		}

		$hash = [
			'_seopress_redirections_type'  => 'redirection_header_code',
			'_seopress_redirections_value' => 'redirection_url_to',
		];
		$this->replace_meta( $hash, null, $object_id, $object_type, 'convert_variables' );

		$redirection = [
			'source'      => trim( parse_url( $source_url, PHP_URL_PATH ), '/' ),
			'destination' => $this->get_meta( $object_type, $object_id, '_seopress_redirections_value' ),
			'code'        => $this->get_meta( $object_type, $object_id, '_seopress_redirections_type' ),
			'status'      => $this->get_meta( $object_type, $object_id, '_seopress_redirections_enabled' ),
		];

		$this->save_redirection( $redirection );
	}

	/**
	 * Get snippet value.
	 *
	 * @param int $post_id  Post ID.
	 * @param int $meta_key Meta key.
	 *
	 * @return string $value Snippet value
	 */
	private function get_snippet_value( $post_id, $meta_key ) {
		$prefix = in_array( $meta_key, [ 'events_offers_valid_from_date', 'events_offers_valid_from_time' ], true ) ? '_seopress_rich_snippets_' : '_seopress_pro_rich_snippets_';
		$value  = get_post_meta( $post_id, $prefix . $meta_key, true );

		if ( in_array( $meta_key, [ 'recipes_prep_time', 'recipes_cook_time', 'videos_duration' ], true ) ) {
			$value .= 'M';
		}

		$hash = [
			'events_start_date'             => 'events_start_time',
			'events_end_date'               => 'events_end_time',
			'events_offers_valid_from_date' => 'events_offers_valid_from_time',
		];

		if ( isset( $hash[ $meta_key ] ) ) {
			$time  = get_post_meta( $post_id, $prefix . $hash[ $meta_key ], true );
			$value = strtotime( $value . ' ' . $time );
		}

		return $value;

	}

	/**
	 * Get schema meta keys.
	 *
	 * @param string $type Type of snippet.
	 *
	 * @return array
	 */
	private function get_schema_metakeys( $type ) {
		$hash = [
			'articles' => [
				'article_type'  => 'article_type',
				'article_title' => 'name',
			],
			'recipes'  => [
				'recipes_name'       => 'name',
				'recipes_desc'       => 'desc',
				'recipes_cat'        => 'recipe_type',
				'recipes_prep_time'  => 'recipe_preptime',
				'recipes_cook_time'  => 'recipe_cooktime',
				'recipes_calories'   => 'recipe_calories',
				'recipes_yield'      => 'recipe_yield',
				'recipes_ingredient' => 'recipe_ingredients',
			],
			'courses'  => [
				'courses_title'   => 'name',
				'courses_desc'    => 'desc',
				'courses_school'  => 'course_provider',
				'courses_website' => 'course_provider_url',
			],
			'videos'   => [
				'videos_name'        => 'name',
				'videos_description' => 'desc',
				'videos_img'         => 'rank_math_twitter_title',
				'videos_url'         => 'video_url',
				'videos_duration'    => 'video_duration',
			],
			'events'   => [
				'events_type'                   => 'event_type',
				'events_name'                   => 'name',
				'events_desc'                   => 'desc',
				'events_location_address'       => 'event_address',
				'events_location_name'          => 'event_venue',
				'events_location_url'           => 'event_venue_url',
				'events_start_date'             => 'event_startdate',
				'events_end_date'               => 'event_enddate',
				'events_offers_price'           => 'event_price',
				'events_offers_price_currency'  => 'event_currency',
				'events_offers_url'             => 'event_ticketurl',
				'events_offers_availability'    => 'event_availability',
				'events_offers_valid_from_date' => 'event_availability_starts',
				'events_performer'              => 'event_performer',
			],
			'products' => [
				'product_description'    => 'desc',
				'product_name'           => 'name',
				'product_price_currency' => 'product_currency',
				'product_price'          => 'product_price',
			],
			'review'   => [
				'review_item'   => 'name',
				'item_name'     => 'desc',
				'review_rating' => 'review_rating_value',
			],
		];

		return isset( $hash[ $type ] ) ? $hash[ $type ] : false;
	}

	/**
	 * Set primary term for post
	 *
	 * @param int[] $post_ids Post IDs.
	 */
	private function set_primary_term( $post_ids ) {
		$post_ids = wp_list_pluck( $post_ids, 'ID' );
		$table    = DB::query_builder( 'postmeta' );
		$results  = $table->whereLike( 'meta_key', '_seopress_robots_primary_cat' )->whereIn( 'post_id', $post_ids )->get();

		foreach ( $results as $result ) {
			if ( 'none' !== $result->meta_value ) {
				update_post_meta( $result->post_id, 'rank_math_primary_category', $result->meta_value );
			}
		}
	}

	/**
	 * Set post/term robots.
	 *
	 * @param int    $object_id   Object id.
	 * @param string $object_type Object type.
	 */
	private function set_object_robots( $object_id, $object_type ) {
		// Early bail if robots data is set in Rank Math plugin.
		if ( ! empty( $this->get_meta( $object_type, $object_id, 'rank_math_robots' ) ) ) {
			return;
		}

		$current     = $this->get_robots_by_hash( $object_id, $object_type );
		$is_noindex  = in_array( 'noindex', $current, true );
		$is_nofollow = in_array( 'nofollow', $current, true );

		if ( ! $is_noindex || ! $is_nofollow ) {
			$robots    = $this->get_default_robots( $object_id, $object_type );
			$current[] = ! $is_nofollow && ! empty( $robots['nofollow'] ) ? 'nofollow' : '';

			// Keep global no index status.
			if ( ! empty( $robots['noindex'] ) ) {
				unset( $current[ 'index' ] );
				$current[] = 'noindex';
			}
		}

		$this->update_meta( $object_type, $object_id, 'rank_math_robots', array_unique( $current ) );
	}

	/**
	 * Get by meta hash.
	 *
	 * @param int    $object_id   Object id.
	 * @param string $object_type Object type.
	 *
	 * @return array Array of robots data.
	 */
	private function get_robots_by_hash( $object_id, $object_type ) {
		$current = [];
		$hash    = [
			'_seopress_robots_index'      => 'noindex',
			'_seopress_robots_follow'     => 'nofollow',
			'_seopress_robots_imageindex' => 'noimageindex',
			'_seopress_robots_archive'    => 'noarchive',
			'_seopress_robots_snippet'    => 'nosnippet',
		];

		foreach ( $hash as $source => $value ) {
			if ( ! empty( $this->get_meta( $object_type, $object_id, $source ) ) ) {
				$current[] = $value;
			}
		}

		return $current;
	}

	/**
	 * Get default robots data from settings.
	 *
	 * @param int    $object_id   Object id.
	 * @param string $object_type Object type.
	 *
	 * @return array Array of robots data.
	 */
	private function get_default_robots( $object_id, $object_type ) {
		$seopress_titles = get_option( 'seopress_titles_option_name' );
		if ( 'post' === $object_type ) {
			$post_type = get_post_type( $object_id );
			return isset( $seopress_titles['seopress_titles_single_titles'][ $post_type ] ) ? $seopress_titles['seopress_titles_single_titles'][ $post_type ] : [];
		}

		$term = get_term( $object_id );
		return isset( $seopress_titles['seopress_titles_tax_titles'][ $term->taxonomy ] ) ? $seopress_titles['seopress_titles_tax_titles'][ $term->taxonomy ] : [];
	}
}
