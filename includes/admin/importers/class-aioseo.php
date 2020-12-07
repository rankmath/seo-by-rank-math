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
use MyThemeShop\Helpers\Str;
use RankMath\Admin\Admin_Helper;

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
	protected $option_keys = [ '_aioseop_%', 'aioseop_options' ];

	/**
	 * Choices keys to import.
	 *
	 * @var array
	 */
	protected $choices = [ 'settings', 'postmeta', 'termmeta' ];

	/**
	 * Import settings of plugin.
	 *
	 * @return bool
	 */
	protected function settings() {
		$this->get_settings();
		$aioseo = get_option( 'aioseop_options' );

		// Titles & Descriptions.
		if ( ! empty( $aioseo['aiosp_home_title'] ) ) {
			$aioseo['aiosp_home_page_title_format'] = $aioseo['aiosp_home_title'];
		}
		$hash = [
			'aiosp_home_page_title_format' => 'homepage_title',
			'aiosp_home_description'       => 'homepage_description',
			'aiosp_author_title_format'    => 'author_archive_title',
			'aiosp_date_title_format'      => 'date_archive_title',
			'aiosp_search_title_format'    => 'search_title',
			'aiosp_404_title_format'       => '404_title',
		];
		$this->replace( $hash, $aioseo, $this->titles, 'convert_variables' );

		// Verification Codes.
		$hash = [
			'aiosp_google_verify'    => 'google_verify',
			'aiosp_bing_verify'      => 'bing_verify',
			'aiosp_pinterest_verify' => 'pinterest_verify',
		];
		$this->replace( $hash, $aioseo, $this->settings );

		$this->post_types_settings();
		$this->taxonomies_settings();
		$this->opengraph_settings();
		$this->sitemap_settings();
		$this->update_settings();

		return true;
	}

	/**
	 * Post Types settings.
	 */
	private function post_types_settings() {
		$hash         = [];
		$aioseo       = get_option( 'aioseop_options' );
		$postnoindex  = isset( $aioseo['aiosp_cpostnoindex'] ) && is_array( $aioseo['aiosp_cpostnoindex'] ) ? $aioseo['aiosp_cpostnoindex'] : [];
		$postnofollow = isset( $aioseo['aiosp_cpostnofollow'] ) && is_array( $aioseo['aiosp_cpostnofollow'] ) ? $aioseo['aiosp_cpostnofollow'] : [];
		if ( empty( $postnoindex ) && empty( $postnofollow ) ) {
			return;
		}

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$hash[ "aiosp_{$post_type}_title_format" ] = "pt_{$post_type}_title";
			$this->set_robots_settings(
				in_array( $post_type, $postnoindex, true ),
				in_array( $post_type, $postnofollow, true ),
				$post_type
			);
		}

		$this->replace( $hash, $aioseo, $this->titles, 'convert_variables' );
	}

	/**
	 * Set global robots.
	 *
	 * @param bool   $noindex   Is noindex set.
	 * @param bool   $nofollow  Is nofollow set.
	 * @param string $post_type Current Post type.
	 */
	private function set_robots_settings( $noindex, $nofollow, $post_type ) {
		if ( ! $noindex && ! $nofollow ) {
			return;
		}

		$this->titles[ "pt_{$post_type}_custom_robots" ] = 'on';
		if ( $noindex ) {
			$this->titles[ "pt_{$post_type}_robots" ][] = 'noindex';
		}
		if ( $nofollow ) {
			$this->titles[ "pt_{$post_type}_robots" ][] = 'nofollow';
		}
		$this->titles[ "pt_{$post_type}_robots" ] = \array_unique( $this->titles[ "pt_{$post_type}_robots" ] );
	}

	/**
	 * Taxonomies settings.
	 */
	private function taxonomies_settings() {
		$hash   = [];
		$aioseo = get_option( 'aioseop_options' );
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$convert = 'post_tag' === $taxonomy ? 'tag' : $taxonomy;

			$hash[ "aiosp_{$convert}_title_format" ] = "tax_{$taxonomy}_title";

			if ( empty( $aioseo[ "aiosp_{$taxonomy}_noindex" ] ) ) {
				continue;
			}

			$this->titles[ "tax_{$taxonomy}_custom_robots" ] = 'on';
			$this->titles[ "tax_{$taxonomy}_robots" ]        = [ 'noindex' ];
			$this->titles[ "tax_{$taxonomy}_robots" ]        = array_unique( $this->titles[ "tax_{$taxonomy}_robots" ] );
		}

		$this->replace( $hash, $aioseo, $this->titles, 'convert_variables' );
	}

	/**
	 * Opengraph settings.
	 */
	private function opengraph_settings() {
		$aioseo = get_option( 'aioseop_options' );
		if ( empty( $aioseo['modules']['aiosp_opengraph_options'] ) || ! is_array( $aioseo['modules']['aiosp_opengraph_options'] ) ) {
			return;
		}

		$opengraph_settings = $aioseo['modules']['aiosp_opengraph_options'];
		$set_meta           = 'on' === $opengraph_settings['aiosp_opengraph_setmeta'];

		$this->titles['homepage_facebook_title']       = $set_meta ? $this->titles['homepage_title'] : $this->convert_variables( $opengraph_settings['aiosp_opengraph_hometitle'] );
		$this->titles['homepage_facebook_description'] = $set_meta ? $this->titles['homepage_description'] : $this->convert_variables( $opengraph_settings['aiosp_opengraph_description'] );

		if ( isset( $opengraph_settings['aiosp_opengraph_homeimage'] ) ) {
			$this->replace_image( $opengraph_settings['aiosp_opengraph_homeimage'], $this->titles, 'homepage_facebook_image', 'homepage_facebook_image_id' );
		}

		$this->titles['facebook_admin_id'] = $opengraph_settings['aiosp_opengraph_key'];
		$this->titles['facebook_app_id']   = $opengraph_settings['aiosp_opengraph_appid'];

		if ( isset( $opengraph_settings['aiosp_opengraph_person_or_org'] ) && ! empty( $opengraph_settings['aiosp_opengraph_person_or_org'] ) ) {
			Helper::update_modules( [ 'local-seo' => 'on' ] );

			$this->titles['knowledgegraph_name'] = $opengraph_settings['aiosp_opengraph_social_name'];
			$this->titles['knowledgegraph_type'] = 'org' === $opengraph_settings['aiosp_opengraph_person_or_org'] ? 'company' : 'person';
		}

		$this->social_links_settings( $opengraph_settings );
	}

	/**
	 * Social Links settings.
	 *
	 * @param array $settings Array of module settings.
	 */
	private function social_links_settings( $settings ) {
		if ( ! isset( $settings['aiosp_opengraph_profile_links'] ) || empty( $settings['aiosp_opengraph_profile_links'] ) ) {
			return;
		}

		$social_links = explode( "\n", $settings['aiosp_opengraph_profile_links'] );
		$social_links = array_filter( $social_links );
		if ( empty( $social_links ) ) {
			return;
		}

		foreach ( $social_links as $social_link ) {
			$this->convert_social_link( $social_link );
		}
	}

	/**
	 * Convert social link.
	 *
	 * @param string $link Link to check.
	 */
	private function convert_social_link( $link ) {
		$services = [ 'facebook', 'twitter' ];
		foreach ( $services as $service ) {
			if ( Str::contains( $service, $social_link ) ) {
				$this->titles[ 'social_url_' . $service ] = $social_link;
				break;
			}
		}
	}

	/**
	 * Sitemap settings.
	 */
	private function sitemap_settings() {
		$aioseo = get_option( 'aioseop_options' );
		if ( empty( $aioseo['modules']['aiosp_sitemap_options'] ) || ! is_array( $aioseo['modules']['aiosp_sitemap_options'] ) ) {
			return;
		}

		$sitemap_settings = $aioseo['modules']['aiosp_sitemap_options'];

		// Sitemap.
		if ( isset( $sitemap_settings['enablexmlsitemap'] ) ) {
			Helper::update_modules( [ 'sitemap' => 'on' ] );
		}
		$hash = [
			'aiosp_sitemap_max_posts'  => 'items_per_page',
			'aiosp_sitemap_excl_pages' => 'exclude_posts',
		];
		$this->replace( $hash, $sitemap_settings, $this->sitemap );

		// Sitemap - Exclude Terms.
		if ( ! empty( $sitemap_settings['aiosp_sitemap_excl_categories'] ) ) {
			$this->sitemap['exclude_terms'] = implode( ',', $sitemap_settings['aiosp_sitemap_excl_categories'] );
		}

		// Sitemap - Author / User.
		$this->titles['disable_author_archives'] = isset( $sitemap_settings['aiosp_sitemap_archive'] ) ? 'on' : 'off';

		$this->sitemap_post_types();
		$this->sitemap_taxonomies();
	}

	/**
	 * Sitemap - Post Types.
	 */
	private function sitemap_post_types() {
		$aioseo = get_option( 'aioseop_options' );
		$all    = in_array( 'all', $sitemap_settings['aiosp_sitemap_posttypes'], true );

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$this->sitemap[ "pt_{$post_type}_sitemap" ] = $all || in_array( $post_type, $sitemap_settings['aiosp_sitemap_posttypes'], true ) ? 'on' : 'off';
		}
	}

	/**
	 * Sitemap - Taxonomies.
	 */
	private function sitemap_taxonomies() {
		$aioseo = get_option( 'aioseop_options' );
		$all    = in_array( 'all', $sitemap_settings['aiosp_sitemap_taxonomies'], true );

		foreach ( Helper::get_accessible_taxonomies() as $taxonomy => $object ) {
			$this->sitemap[ "tax_{$taxonomy}_sitemap" ] = $all || in_array( $taxonomy, $sitemap_settings['aiosp_sitemap_taxonomies'], true ) ? 'on' : 'off';
		}
	}

	/**
	 * Import post meta of plugin.
	 *
	 * @return array
	 */
	protected function postmeta() {
		$this->set_pagination( $this->get_post_ids( true ) );
		$post_ids = $this->get_post_ids();

		$hash = [
			'_aioseop_title'       => 'rank_math_title',
			'_aioseop_keywords'    => 'rank_math_focus_keyword',
			'_aioseop_description' => 'rank_math_description',
			'_aioseop_custom_link' => 'rank_math_canonical_url',
		];
		foreach ( $post_ids as $post ) {
			$post_id = $post->ID;
			$this->replace_meta( $hash, null, $post_id, 'post' );
			$this->set_object_robots( $post_id, 'post' );

			$opengraph_meta = get_post_meta( $post_id, '_aioseop_opengraph_settings', true );
			if ( ! empty( $opengraph_meta ) && is_array( $opengraph_meta ) ) {
				$this->set_opengraph( $opengraph_meta, 'post', $post_id );
			}
		}

		return $this->get_pagination_arg();
	}

	/**
	 * Import term meta of plugin.
	 *
	 * @return array
	 */
	protected function termmeta() {
		$terms = new \WP_Term_Query(
			[
				'meta_key'   => '_aioseop_title',
				'fields'     => 'ids',
				'hide_empty' => false,
				'get'        => 'all',
			]
		);

		if ( empty( $terms ) || is_wp_error( $terms ) ) {
			return false;
		}

		$hash = [
			'_aioseop_title'       => 'rank_math_title',
			'_aioseop_keywords'    => 'rank_math_focus_keyword',
			'_aioseop_description' => 'rank_math_description',
			'_aioseop_custom_link' => 'rank_math_canonical_url',
		];

		foreach ( $terms->get_terms() as $term_id ) {
			$count++;

			$this->replace_meta( $hash, null, $term_id, 'term' );
			$this->set_object_robots( $term_id, 'term' );

			$opengraph_meta = get_term_meta( $term_id, '_aioseop_opengraph_settings', true );
			if ( ! empty( $opengraph_meta ) && is_array( $opengraph_meta ) ) {
				$this->set_opengraph( $opengraph_meta, 'term', $term_id );
			}
		}

		return compact( 'count' );
	}

	/**
	 * Set OpenGraph.
	 *
	 * @param array  $opengraph_meta Open Graph meta.
	 * @param string $object_type    Current Object type.
	 * @param int    $object_id      Object ID.
	 */
	private function set_opengraph( $opengraph_meta, $object_type, $object_id ) {

		if ( ! empty( $opengraph_meta['aioseop_opengraph_settings_title'] ) ) {
			$this->update_meta( $object_type, $object_id, 'rank_math_facebook_title', $opengraph_meta['aioseop_opengraph_settings_title'] );
			$this->update_meta( $object_type, $object_id, 'rank_math_twitter_title', $opengraph_meta['aioseop_opengraph_settings_title'] );
		}

		if ( ! empty( $opengraph_meta['aioseop_opengraph_settings_desc'] ) ) {
			$this->update_meta( $object_type, $object_id, 'rank_math_facebook_description', $opengraph_meta['aioseop_opengraph_settings_desc'] );
			$this->update_meta( $object_type, $object_id, 'rank_math_twitter_description', $opengraph_meta['aioseop_opengraph_settings_desc'] );
		}

		$og_thumb = ! empty( $opengraph_meta['aioseop_opengraph_settings_customimg'] ) ? $opengraph_meta['aioseop_opengraph_settings_customimg'] : ( ! empty( $opengraph_meta['aioseop_opengraph_settings_image'] ) ? $opengraph_meta['aioseop_opengraph_settings_image'] : '' );
		$this->replace_image( $og_thumb, $object_type, 'rank_math_facebook_image', 'rank_math_facebook_image_id', $object_id );

		if ( ! empty( $opengraph_meta['aioseop_opengraph_settings_setcard'] ) ) {
			$twitter_card_type = 'summary' === $opengraph_meta['aioseop_opengraph_settings_setcard'] ? 'summary_card' : 'summary_large_image';
			$this->update_meta( $object_type, $object_id, 'rank_math_twitter_card_type', $twitter_card_type );
		}
	}

	/**
	 * Set object robots meta.
	 *
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Current Object type.
	 */
	private function set_object_robots( $object_id, $object_type ) {
		// Early bail if robots data is set in Rank Math plugin.
		if ( ! empty( $this->get_meta( $object_type, $object_id, 'rank_math_robots' ) ) ) {
			return;
		}

		// ROBOTS.
		$robots   = [];
		$aioseo   = get_option( 'aioseop_options' );
		$robots[] = $this->get_noindex_robot( $object_id, $object_type, $aioseo );
		$robots[] = $this->get_follow_robot( $object_id, $object_type, $aioseo );

		$this->update_meta( $object_type, $object_id, 'rank_math_robots', array_unique( $robots ) );
	}

	/**
	 * Get noindex robot.
	 *
	 * @param int    $object_id    Object ID.
	 * @param string $object_type Current Object type.
	 * @param array  $aioseo       Option array.
	 *
	 * @return string
	 */
	private function get_noindex_robot( $object_id, $object_type, $aioseo ) {
		$noindex         = $this->get_meta( $object_type, $object_id, '_aioseop_noindex' );
		$exclude_sitemap = $this->is_object_excluded_sitemap( $object_id, $object_type );

		if ( empty( $noindex ) ) {
			$noindex = 'post' === $object_type && in_array( get_post_type( $object_id ), $aioseo['aiosp_cpostnoindex'], true ) ? 'noindex' : 'index';
			return $exclude_sitemap ? 'noindex' : $noindex;
		}

		return 'on' === $noindex || $exclude_sitemap ? 'noindex' : 'index';
	}

	/**
	 * Get follow robot.
	 *
	 * @param int    $object_id     Object ID.
	 * @param string $object_type Current Object type.
	 * @param array  $aioseo      Option array.
	 *
	 * @return string
	 */
	private function get_follow_robot( $object_id, $object_type, $aioseo ) {
		$nofollow = $this->get_meta( $object_type, $object_id, '_aioseop_nofollow' );

		if ( empty( $nofollow ) && 'post' === $object_type ) {
			return in_array( get_post_type( $object_id ), $aioseo['aiosp_cpostnofollow'], true ) ? 'nofollow' : '';
		}

		return 'on' === $nofollow ? 'nofollow' : '';
	}

	/**
	 * Is object excluded from sitemap.
	 *
	 * @param int    $object_id   Object ID.
	 * @param string $object_type Current Object type.
	 *
	 * @return bool
	 */
	private function is_object_excluded_sitemap( $object_id, $object_type ) {
		$exclude_sitemap = $this->get_meta( $object_type, $object_id, '_aioseop_sitemap_exclude' );
		return 'on' === $exclude_sitemap ? true : false;
	}

	/**
	 * Get the actions which can be performed for the plugin.
	 *
	 * @return array
	 */
	public function get_choices() {
		return [
			'settings' => esc_html__( 'Import Settings', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import AIO SEO plugin settings, global meta, sitemap settings, etc.', 'rank-math' ) ),
			'postmeta' => esc_html__( 'Import Post Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information of your posts/pages like the titles, descriptions, robots meta, OpenGraph info, etc.', 'rank-math' ) ),
			'termmeta' => esc_html__( 'Import Term Meta', 'rank-math' ) . Admin_Helper::get_tooltip( esc_html__( 'Import meta information of your terms like the titles, descriptions, robots meta, OpenGraph info, etc.', 'rank-math' ) ),
		];
	}

	/**
	 * Convert Yoast / AIO SEO variables if needed.
	 *
	 * @param string $string Value to convert.
	 * @return string
	 */
	public function convert_variables( $string ) {
		$string = str_replace( '%blog_title%', '%sitename%', $string );
		$string = str_replace( '%blog_description%', '%sitedesc%', $string );
		$string = str_replace( '%post_title%', '%title%', $string );
		$string = str_replace( '%page_title%', '%title%', $string );
		$string = str_replace( '%category_title%', '%category%', $string );
		$string = str_replace( '%category_description%', '%term_description%', $string );
		$string = str_replace( '%archive_title%', '%term%', $string );
		$string = str_replace( '%category%', '%category%', $string );
		$string = str_replace( '%post_author_login%', '%name%', $string );
		$string = str_replace( '%post_author_nicename%', '%name%', $string );
		$string = str_replace( '%post_author_firstname%', '%name%', $string );
		$string = str_replace( '%post_author_lastname%', '%name%', $string );
		$string = str_replace( '%current_date%', '%currentdate%', $string );
		$string = str_replace( '%post_date%', '%date%', $string );
		$string = str_replace( '%post_year%', '%date(Y)%', $string );
		$string = str_replace( '%post_month%', '%date(M)%', $string );
		$string = str_replace( '%page_author_login%', '%name%', $string );
		$string = str_replace( '%page_author_nicename%', '%name%', $string );
		$string = str_replace( '%page_author_firstname%', '%name%', $string );
		$string = str_replace( '%page_author_lastname%', '%name%', $string );
		$string = str_replace( '%author%', '%name%', $string );
		$string = str_replace( '%search%', '%search_query%', $string );

		return str_replace( '%%', '%', $string );
	}
}
