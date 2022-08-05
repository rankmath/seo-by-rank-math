<?php
/**
 * The post metabox screen.
 *
 * @since      1.0.25
 * @package    RankMath
 * @subpackage RankMath\Admin\Metabox
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin\Metabox;

use RankMath\KB;
use RankMath\Helper;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Editor;
use RankMath\Frontend_SEO_Score;
use RankMath\Admin\Admin_Helper;
use MyThemeShop\Helpers\Str;
use MyThemeShop\Helpers\Url;
use MyThemeShop\Helpers\WordPress;

defined( 'ABSPATH' ) || exit;

/**
 * Post metabox class.
 */
class Post_Screen implements IScreen {

	use Hooker;

	/**
	 * Hold primary taxonomy
	 *
	 * @var object
	 */
	private $primary_taxonomy = null;

	/**
	 * Class construct
	 */
	public function __construct() {
		$this->filter( 'rank_math/researches/tests', 'remove_tests', 10, 2 );
	}

	/**
	 * Get object id
	 *
	 * @return int
	 */
	public function get_object_id() {
		global $post;

		return $post->ID;
	}

	/**
	 * Get object type
	 *
	 * @return string
	 */
	public function get_object_type() {
		return 'post';
	}

	/**
	 * Get object types to register metabox to
	 *
	 * @return array
	 */
	public function get_object_types() {
		return Helper::get_allowed_post_types();
	}

	/**
	 * Enqueue Styles and Scripts required for screen.
	 */
	public function enqueue() {
		$is_elementor    = Helper::is_elementor_editor();
		$is_block_editor = Helper::is_block_editor() && \rank_math_is_gutenberg();

		Helper::add_json( 'postType', get_post_type() );

		if ( ! $is_elementor ) {
			$this->enqueue_custom_fields();
		}

		wp_register_script(
			'rank-math-formats',
			rank_math()->plugin_url() . 'assets/admin/js/gutenberg-formats.js',
			[],
			rank_math()->version,
			true
		);

		if ( $is_block_editor || $is_elementor ) {
			$this->enqueue_commons();
		}

		if ( $is_block_editor && ! $is_elementor && Editor::can_add_editor() ) {
			$this->enqueue_for_gutenberg();
			return;
		}

		if ( $is_elementor ) {
			return;
		}

		// Classic.
		if ( Helper::is_block_editor() ) {
			wp_enqueue_script( 'rank-math-formats' );
		}

		if ( $is_block_editor ) {
			wp_enqueue_script( 'rank-math-primary-term', rank_math()->plugin_url() . 'assets/admin/js/gutenberg-primary-term.js', [], rank_math()->version, true );
		}
	}

	/**
	 * Get values for localize.
	 *
	 * @return array
	 */
	public function get_values() {
		$post_type = $this->get_current_post_type();

		return [
			'parentDomain'           => Url::get_domain( home_url() ),
			'noFollowDomains'        => Str::to_arr_no_empty( Helper::get_settings( 'general.nofollow_domains' ) ),
			'noFollowExcludeDomains' => Str::to_arr_no_empty( Helper::get_settings( 'general.nofollow_exclude_domains' ) ),
			'noFollowExternalLinks'  => Helper::get_settings( 'general.nofollow_external_links' ),
			'featuredImageNotice'    => esc_html__( 'The featured image should be at least 200 by 200 pixels to be picked up by Facebook and other social media sites.', 'rank-math' ),
			'pluginReviewed'         => $this->plugin_reviewed(),
			'postSettings'           => [
				'linkSuggestions' => Helper::get_settings( 'titles.pt_' . $post_type . '_link_suggestions' ),
				'useFocusKeyword' => 'focus_keywords' === Helper::get_settings( 'titles.pt_' . $post_type . '_ls_use_fk' ),
			],
			'frontEndScore'          => Frontend_SEO_Score::show_on(),
			'postName'               => get_post_field( 'post_name', get_post() ),
			'permalinkFormat'        => $this->get_permalink_format(),
			'assessor'               => [
				'hasTOCPlugin'     => $this->has_toc_plugin(),
				'sentimentKbLink'  => KB::get( 'sentiments' ),
				'focusKeywordLink' => admin_url( 'edit.php?focus_keyword=%focus_keyword%&post_type=%post_type%' ),
				'isUserEdit'       => Admin_Helper::is_user_edit(),
				'socialPanelLink'  => Helper::get_admin_url( 'options-titles#setting-panel-social' ),
				'primaryTaxonomy'  => $this->get_primary_taxonomy(),
			],
		];
	}

	/**
	 * Get object values for localize
	 *
	 * @return array
	 */
	public function get_object_values() {
		global $post;

		return [
			'primaryTerm'         => $this->get_primary_term_id(),
			'authorName'          => get_the_author_meta( 'display_name', $post->post_author ),
			'titleTemplate'       => Helper::get_settings( "titles.pt_{$post->post_type}_title", '%title% %sep% %sitename%' ),
			'descriptionTemplate' => Helper::get_settings( "titles.pt_{$post->post_type}_description", '' ),
			'showScoreFrontend'   => ! Helper::get_post_meta( 'dont_show_seo_score', $this->get_object_id() ),
		];
	}

	/**
	 * Get analysis to run.
	 *
	 * @return array
	 */
	public function get_analysis() {
		$tests = [
			'contentHasTOC'             => true,
			'contentHasShortParagraphs' => true,
			'contentHasAssets'          => true,
			'keywordInTitle'            => true,
			'keywordInMetaDescription'  => true,
			'keywordInPermalink'        => true,
			'keywordIn10Percent'        => true,
			'keywordInContent'          => true,
			'keywordInSubheadings'      => true,
			'keywordInImageAlt'         => true,
			'keywordDensity'            => true,
			'keywordNotUsed'            => true,
			'lengthContent'             => true,
			'lengthPermalink'           => true,
			'linksHasInternal'          => true,
			'linksHasExternals'         => true,
			'linksNotAllExternals'      => true,
			'titleStartWithKeyword'     => true,
			'titleSentiment'            => true,
			'titleHasPowerWords'        => true,
			'titleHasNumber'            => true,
			'hasContentAI'              => true,
		];

		return $tests;
	}

	/**
	 * Remove few tests on static Homepage.
	 *
	 * @since 1.0.42
	 *
	 * @param array  $tests Array of tests with score.
	 * @param string $type  Object type. Can be post, user or term.
	 */
	public function remove_tests( $tests, $type ) {
		if ( ! Admin_Helper::is_home_page() && ! Admin_Helper::is_posts_page() ) {
			return $tests;
		}

		return array_diff_assoc( $tests, $this->exclude_tests() );
	}

	/**
	 * Function to get the permalink format.
	 *
	 * @since 1.0.69.2
	 */
	private function get_permalink_format() {
		$post_id = $this->get_object_id();
		$post    = get_post( $post_id );

		if ( 'attachment' === $post->post_type ) {
			return str_replace( $post->post_name, '%postname%', get_permalink( $post ) );
		}

		if ( 'auto-draft' !== $post->post_status || 'post' !== $post->post_type ) {
			$sample_permalink = get_sample_permalink( $post_id, null, null );
			return isset( $sample_permalink[0] ) ? $sample_permalink[0] : home_url();
		}

		$post_temp              = $post;
		$post_temp->post_status = 'publish';
		return get_permalink( $post_temp, true );
	}

	/**
	 * Tests to exclude on Homepage and Blog page.
	 *
	 * @since 1.0.43
	 *
	 * @return array Array of excluded tests.
	 */
	private function exclude_tests() {
		if ( Admin_Helper::is_home_page() ) {
			return [
				'contentHasTOC'        => true,
				'keywordInPermalink'   => true,
				'lengthPermalink'      => true,
				'linksHasExternals'    => true,
				'linksNotAllExternals' => true,
				'titleSentiment'       => true,
				'titleHasPowerWords'   => true,
				'titleHasNumber'       => true,
			];
		}

		return [
			'contentHasTOC'             => true,
			'contentHasShortParagraphs' => true,
			'keywordIn10Percent'        => true,
			'keywordInContent'          => true,
			'keywordInSubheadings'      => true,
			'keywordDensity'            => true,
			'lengthContent'             => true,
			'linksHasInternal'          => true,
			'linksHasExternals'         => true,
			'linksNotAllExternals'      => true,
		];
	}

	/**
	 * Enqueque scripts common for all builders.
	 */
	private function enqueue_commons() {
		wp_register_style( 'rank-math-editor', rank_math()->plugin_url() . 'assets/admin/css/gutenberg.css', [], rank_math()->version );
	}

	/**
	 * Enqueue script to analyze custom fields data.
	 */
	private function enqueue_custom_fields() {
		global $post;

		$custom_fields = Str::to_arr_no_empty( Helper::get_settings( 'titles.pt_' . $post->post_type . '_analyze_fields' ) );
		if ( empty( $custom_fields ) ) {
			return;
		}

		$file = Helper::is_block_editor() ? 'glue-custom-fields.js' : 'custom-fields.js';

		wp_enqueue_script( 'rank-math-custom-fields', rank_math()->plugin_url() . 'assets/admin/js/' . $file, [ 'wp-hooks', 'rank-math-analyzer' ], rank_math()->version, true );
		Helper::add_json( 'analyzeFields', $custom_fields );
	}

	/**
	 * Enqueue scripts for gutenberg screen.
	 */
	private function enqueue_for_gutenberg() {
		wp_enqueue_style( 'rank-math-editor' );
		wp_enqueue_script( 'rank-math-formats' );
		wp_enqueue_script(
			'rank-math-editor',
			rank_math()->plugin_url() . 'assets/admin/js/gutenberg.js',
			[
				'clipboard',
				'wp-autop',
				'wp-blocks',
				'wp-components',
				'wp-editor',
				'wp-edit-post',
				'wp-element',
				'wp-i18n',
				'wp-plugins',
				'wp-wordcount',
				'rank-math-analyzer',
				'rank-math-app',
			],
			rank_math()->version,
			true
		);
	}

	/**
	 * Get current post type.
	 *
	 * @return string
	 */
	private function get_current_post_type() {
		$post_type = get_post_type();
		if ( function_exists( 'get_current_screen' ) ) {
			$screen    = get_current_screen();
			$post_type = isset( $screen->post_type ) ? $screen->post_type : $post_type;
		}

		return $post_type;
	}

	/**
	 * Check if any TOC plugin detected
	 *
	 * @return bool
	 */
	private function has_toc_plugin() {
		if ( \defined( 'ELEMENTOR_PRO_VERSION' ) ) {
			return true;
		}

		$plugins_found  = [];
		$active_plugins = get_option( 'active_plugins' );
		$active_plugins = is_multisite() ? array_merge( $active_plugins, array_keys( get_site_option( 'active_sitewide_plugins', [] ) ) ) : $active_plugins;

		/**
		 * Allow developers to add plugins to the TOC list.
		 *
		 * @param array TOC plugins.
		 */
		$toc_plugins = $this->do_filter(
			'researches/toc_plugins',
			[
				'wp-shortcode/wp-shortcode.php'         => 'WP Shortcode by MyThemeShop',
				'wp-shortcode-pro/wp-shortcode-pro.php' => 'WP Shortcode Pro by MyThemeShop',
			]
		);

		foreach ( $toc_plugins as $plugin_slug => $plugin_name ) {
			if ( in_array( $plugin_slug, $active_plugins, true ) !== false ) {
				$plugins_found[ $plugin_slug ] = $plugin_name;
			}
		}

		return empty( $plugins_found ) ? false : $plugins_found;
	}

	/**
	 * Plugin already reviewed.
	 *
	 * @return bool
	 */
	private function plugin_reviewed() {
		return get_option( 'rank_math_already_reviewed' ) || current_time( 'timestamp' ) < get_option( 'rank_math_install_date' ) + ( 2 * WEEK_IN_SECONDS );
	}

	/**
	 * Get primary taxonomy.
	 *
	 * @return bool|array
	 */
	private function get_primary_taxonomy() {
		if ( ! is_null( $this->primary_taxonomy ) ) {
			return $this->primary_taxonomy;
		}

		$taxonomy  = false;
		$post_type = $this->get_current_post_type();

		/**
		 * Filter: Allow disabling the primary term feature.
		 * 'rank_math/primary_term' is deprecated,
		 * use 'rank_math/admin/disable_primary_term' instead.
		 *
		 * @param bool $return True to disable.
		 */
		if ( false === apply_filters_deprecated( 'rank_math/primary_term', [ false ], '1.0.43', 'rank_math/admin/disable_primary_term' )
			&& false === $this->do_filter( 'admin/disable_primary_term', false ) ) {
			$taxonomy = Helper::get_settings( 'titles.pt_' . $post_type . '_primary_taxonomy', false );
		}

		if ( ! $taxonomy ) {
			return false;
		}

		$taxonomy = get_taxonomy( $taxonomy );

		$this->primary_taxonomy = [
			'title'         => $taxonomy->labels->singular_name,
			'name'          => $taxonomy->name,
			'singularLabel' => $taxonomy->labels->singular_name,
			'restBase'      => ( $taxonomy->rest_base ) ? $taxonomy->rest_base : $taxonomy->name,
		];

		return $this->primary_taxonomy;
	}

	/**
	 * Get primary term ID.
	 *
	 * @return int
	 */
	private function get_primary_term_id() {
		$taxonomy = $this->get_primary_taxonomy();
		if ( ! $taxonomy ) {
			return 0;
		}

		$id = Helper::get_post_meta( 'primary_' . $taxonomy['name'], $this->get_object_id() );

		return $id ? absint( $id ) : 0;
	}
}
