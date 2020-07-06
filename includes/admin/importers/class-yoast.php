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
use MyThemeShop\Helpers\DB;
use MyThemeShop\Helpers\WordPress;
use RankMath\Redirections\Redirection;
use RankMath\Tools\Yoast_Blocks;

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
	protected $choices = [ 'settings', 'postmeta', 'termmeta', 'usermeta', 'redirections', 'blocks' ];

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

		// Knowledge Graph Logo.
		if ( isset( $yoast_main['company_logo'] ) ) {
			$this->replace_image( $yoast_main['company_logo'], $this->titles, 'knowledgegraph_logo', 'knowledgegraph_logo_id' );
		}
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
		];
		$this->replace( $hash, $yoast_titles, $this->titles, 'convert_variables' );

		$this->local_seo_settings();
		$this->set_separator( $yoast_titles );
		$this->set_post_types( $yoast_titles );
		$this->set_taxonomies( $yoast_titles );
		$this->sitemap_settings( $yoast_main, $yoast_sitemap );
		$this->social_webmaster_settings( $yoast_main, $yoast_social );
		$this->breadcrumb_settings( $yoast_titles, $yoast_internallinks );
		$this->misc_settings( $yoast_titles, $yoast_social );
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

			$this->set_post_robots( $post_id );
			$this->replace_image( get_post_meta( $post_id, '_yoast_wpseo_opengraph-image', true ), 'post', 'rank_math_facebook_image', 'rank_math_facebook_image_id', $post_id );
			$this->replace_image( get_post_meta( $post_id, '_yoast_wpseo_twitter-image', true ), 'post', 'rank_math_twitter_image', 'rank_math_twitter_image_id', $post_id );
			$this->set_post_focus_keyword( $post_id );
			$this->is_twitter_using_facebook( 'post', $post_id );
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Set post robots.
	 *
	 * @param int $post_id Post id.
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
	 * @param int $post_id        Post id.
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
	 * @param int $post_id Post id.
	 */
	private function set_post_focus_keyword( $post_id ) {
		$extra_fks = get_post_meta( $post_id, '_yoast_wpseo_focuskeywords', true );
		$extra_fks = json_decode( $extra_fks, true );
		if ( empty( $extra_fks ) ) {
			return;
		}

		$extra_fks = implode( ', ', array_map( [ $this, 'map_focus_keyword' ], $extra_fks ) );
		$main_fk   = get_post_meta( $post_id, 'rank_math_focus_keyword', true );
		update_post_meta( $post_id, 'rank_math_focus_keyword', $main_fk . ', ' . $extra_fks );
	}

	/**
	 * Return Focus Keyword from entry.
	 *
	 * @param  array $entry Yoast focus keyword entry.
	 * @return string
	 */
	public function map_focus_keyword( $entry ) {
		return $entry['keyword'];
	}

	/**
	 * Set primary term for the posts.
	 *
	 * @param int[] $post_ids Post ids.
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
	 * @param int   $term_id Term id.
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
	 * @param int   $term_id Term id.
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
		$hash = [
			'company_name'      => 'knowledgegraph_name',
			'company_or_person' => 'knowledgegraph_type',
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
	 * Sitemap exclude roles.
	 *
	 * @param array $yoast_sitemap Settings.
	 */
	private function sitemap_exclude_roles( $yoast_sitemap ) {
		foreach ( WordPress::get_roles() as $role => $label ) {
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
	 * @param array $yoast_local Array of yoast local seo settings.
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
	 * @param array $yoast_local Array of yoast local seo settings.
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
			'alexaverify'     => 'alexa_verify',
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
		$yoast_rss = get_option( 'wpseo_rss' );
		$hash      = [
			'rssbefore' => 'rss_before_content',
			'rssafter'  => 'rss_after_content',
		];
		$this->replace( $hash, $yoast_rss, $this->settings, 'convert_variables' );
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
