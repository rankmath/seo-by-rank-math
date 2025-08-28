<?php
/**
 * The option center of the plugin.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\KB;
use RankMath\CMB2;
use RankMath\Helper;
use RankMath\Runner;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Arr;
use RankMath\Helpers\Param;
use RankMath\Wizard\Search_Console;
use RankMath\Admin\Sanitize_Settings;

defined( 'ABSPATH' ) || exit;

/**
 * Option_Center class.
 */
class Option_Center implements Runner {

	use Hooker;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'init', 'register_general_settings', 125 );
		$this->action( 'init', 'register_title_settings', 125 );
		$this->filter( 'rank_math/settings/title', 'title_post_type_settings', 1 );
		$this->filter( 'rank_math/settings/title', 'title_taxonomy_settings', 1 );
		$this->filter( 'rank_math/settings/general', 'remove_unwanted_general_tabs', 1 );
		$this->action( 'admin_enqueue_scripts', 'enqueue_settings_translations', 11 );
	}

	/**
	 * General Settings.
	 */
	public function register_general_settings() {
		$tabs = [
			'links'       => [
				'icon'  => 'rm-icon rm-icon-link',
				'title' => esc_html__( 'Links', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'  => sprintf( esc_html__( 'Change how some of the links open and operate on your website. %s.', 'rank-math' ), '<a href="' . KB::get( 'link-settings', 'Options Panel Links Tab' ) . '" target="_blank">' . esc_html__( 'Learn More', 'rank-math' ) . '</a>' ),
			],
			'breadcrumbs' => [
				'icon'      => 'rm-icon rm-icon-direction',
				'title'     => esc_html__( 'Breadcrumbs', 'rank-math' ),
				'classes'   => 'rank-math-advanced-option',
				/* translators: Link to kb article */
				'desc'      => sprintf( esc_html__( 'Here you can set up the breadcrumbs function. %s', 'rank-math' ), '<a href="' . KB::get( 'breadcrumbs', 'Options Panel Breadcrumbs Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>. <br/>' ),
				'after_row' => current_theme_supports( 'rank-math-breadcrumbs' ) ? '' : '<div class="notice notice-alt notice-warning warning inline rank-math-notice"><p>' . esc_html__( 'Use the following code in your theme template files to display breadcrumbs.', 'rank-math' ) . ' <a href="' . KB::get( 'breadcrumbs-install', 'Options Panel Breadcrumbs Tab' ) . '" target="_blank">' . esc_html__( 'Learn More', 'rank-math' ) . '</a><br /><code>&lt;?php if (function_exists(\'rank_math_the_breadcrumbs\')) rank_math_the_breadcrumbs(); ?&gt;</code> OR <code>[rank_math_breadcrumb]</code></p></div>',
			],
			'webmaster'   => [
				'icon'  => 'rm-icon rm-icon-toolbox',
				'title' => esc_html__( 'Webmaster Tools', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'  => sprintf( esc_html__( 'Enter verification codes for third-party webmaster tools. %s', 'rank-math' ), '<a href="' . KB::get( 'webmaster-tools', 'Options Panel Webmaster Tools Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.<br />' ),
			],
			'others'      => [
				'icon'    => 'rm-icon rm-icon-misc',
				'title'   => esc_html__( 'Others', 'rank-math' ),
				/* translators: Link to kb article */
				'desc'    => sprintf( esc_html__( 'Change some uncommon but essential settings here. %s.', 'rank-math' ), '<a href="' . KB::get( 'other-settings', 'Options Panel Others Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'classes' => 'rank-math-advanced-option',
			],
		];

		if ( is_super_admin() && 'rank-math-options-general' === Param::get( 'page' ) ) {
			Arr::insert(
				$tabs,
				[
					'htaccess' => [
						'icon'    => 'rm-icon rm-icon-htaccess',
						'title'   => esc_html__( 'Edit .htaccess', 'rank-math' ),
						/* translators: Link to kb article */
						'desc'    => sprintf( esc_html__( 'Edit the contents of your .htaccess file easily. %s.', 'rank-math' ), '<a href="' . KB::get( 'edit-htaccess', 'Options Panel htaccess Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
						'classes' => 'rank-math-advanced-option',
						'json'    => [
							'htaccessData' => Admin_Helper::get_htaccess_data(),
						],
					],
				],
				5
			);
		}

		/**
		 * Allow developers to add new sections in the General Settings.
		 *
		 * @param array $tabs
		 */
		$tabs = $this->do_filter( 'settings/general', $tabs );

		new Register_Options_Page(
			[
				'key'        => 'rank-math-options-general',
				'title'      => esc_html__( 'SEO Settings', 'rank-math' ),
				'menu_title' => esc_html__( 'General Settings', 'rank-math' ),
				'capability' => 'rank_math_general',
				'folder'     => 'general',
				'tabs'       => $tabs,
			]
		);
	}

	/**
	 * Remove unneeded tabs from the General Settings.
	 *
	 * @param  array $tabs Hold tabs for optional panel.
	 * @return array
	 */
	public function remove_unwanted_general_tabs( $tabs ) {
		if ( is_multisite() ) {
			unset( $tabs['robots'] );
		}

		if ( ! Helper::has_cap( 'edit_htaccess' ) && is_multisite() ) {
			unset( $tabs['htaccess'] );
		}

		return $tabs;
	}

	/**
	 * Register SEO Titles & Meta Settings.
	 */
	public function register_title_settings() {
		$homepage_notice = '';
		if ( 'page' === get_option( 'show_on_front' ) ) {
			$home_page_id = get_option( 'page_on_front' );
			if ( ! $home_page_id ) {
				$home_page_id = get_option( 'page_for_posts' );
			}

			$homepage_notice = '<a href="' . admin_url( 'post.php?post=' . $home_page_id . '&action=edit' ) . '">' . esc_html__( 'Edit Page: ', 'rank-math' ) . get_the_title( $home_page_id ) . '</a>';
		}
		$tabs = [
			'global'   => [
				'icon'  => 'rm-icon rm-icon-settings',
				'title' => esc_html__( 'Global Meta', 'rank-math' ),
				/* translators: Link to KB article */
				'desc'  => sprintf( esc_html__( 'Change Global meta settings that take effect across your website. %s.', 'rank-math' ), '<a href="' . KB::get( 'titles-meta', 'Options Panel Meta Global Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'json'  => [
					'overlayImages' => array_merge( [ '' => __( 'Off', 'rank-math' ) ], Helper::choices_overlay_images( 'names' ) ),
				],
			],
			'local'    => [
				'icon'      => 'rm-icon rm-icon-local-seo',
				'title'     => esc_html__( 'Local SEO', 'rank-math' ),
				/* translators: Redirection page url */
				'desc'      => sprintf( wp_kses_post( __( 'Optimize for local searches and Knowledge Graph using these settings. %s.', 'rank-math' ) ), '<a href="' . KB::get( 'local-seo-settings', 'Options Panel Meta Local Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'after_row' => '<div class="notice notice-alt notice-info info inline rank-math-notice"><p>' . __( 'Use the <code>[rank_math_contact_info]</code> shortcode to display contact information in a nicely formatted way. You should also claim your business on Google if you have not already.', 'rank-math' ) . '</p></div>',
			],
			'social'   => [
				'icon'  => 'rm-icon rm-icon-social',
				'title' => esc_html__( 'Social Meta', 'rank-math' ),
				/* translators: Link to social setting KB article */
				'desc'  => sprintf( esc_html__( "Add social account information to your website's Schema and Open Graph. %s.", 'rank-math' ), '<a href="' . KB::get( 'social-meta-settings', 'Options Panel Meta Social Tab' ) . '" target="_blank">' . esc_html__( 'Learn More', 'rank-math' ) . '</a>' ),
			],
			'homepage' => [
				'icon'  => 'rm-icon rm-icon-home',
				'title' => esc_html__( 'Homepage', 'rank-math' ),
				'desc'  => sprintf(
					/* translators: Link to KB article */
					esc_html__( 'Add SEO meta and OpenGraph details to your homepage. %s.', 'rank-math' ),
					'<a href="' . KB::get( 'homepage-settings', 'Options Panel Meta Home Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>'
				),
				'json'  => [
					'staticHomePageNotice' => $homepage_notice,
				],
			],
			'author'   => [
				'icon'  => 'rm-icon rm-icon-users',
				'title' => esc_html__( 'Authors', 'rank-math' ),
				/* translators: Link to KB article */
				'desc'  => sprintf( esc_html__( 'Change SEO options related to the author archives. %s.', 'rank-math' ), '<a href="' . KB::get( 'author-settings', 'Options Panel Meta Author Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
				'json'  => [
					'disableAutorArchive' => $this->do_filter( 'settings/titles/disable_author_archives', 'off' ),
				],
			],
			'misc'     => [
				'icon'  => 'rm-icon rm-icon-misc',
				'title' => esc_html__( 'Misc Pages', 'rank-math' ),
				/* translators: Link to KB article */
				'desc'  => sprintf( esc_html__( 'Customize SEO meta settings of pages like search results, 404s, etc. %s.', 'rank-math' ), '<a href="' . KB::get( 'misc-settings', 'Options Panel Meta Misc Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
			],
		];

		/**
		 * Allow developers to add new section in the Title Settings.
		 *
		 * @param array $tabs
		 */
		$tabs = $this->do_filter( 'settings/title', $tabs );

		new Register_Options_Page(
			[
				'key'        => 'rank-math-options-titles',
				'title'      => esc_html__( 'SEO Titles &amp; Meta', 'rank-math' ),
				'menu_title' => esc_html__( 'Titles &amp; Meta', 'rank-math' ),
				'capability' => 'rank_math_titles',
				'folder'     => 'titles',
				'tabs'       => $tabs,
			]
		);

		if ( is_admin() ) {
			Helper::add_json( 'postTitle', 'Post Title' );
			Helper::add_json( 'postUri', home_url( '/post-title' ) );
			Helper::add_json( 'blogName', get_bloginfo( 'name' ) );
		}
	}

	/**
	 * Add post type tabs in the Title Settings panel.
	 *
	 * @param  array $tabs Holds the tabs of the options panel.
	 * @return array
	 */
	public function title_post_type_settings( $tabs ) {
		$icons = Helper::choices_post_type_icons();
		$links = [
			'post'       => '<a href="' . KB::get( 'post-settings', 'Options Panel Meta Posts Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'page'       => '<a href="' . KB::get( 'page-settings', 'Options Panel Meta Pages Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'product'    => '<a href="' . KB::get( 'product-settings', 'Options Panel Meta Products Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'attachment' => '<a href="' . KB::get( 'media-settings', 'Options Panel Meta Attachments Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
		];

		$names = [
			'post'       => 'single %s',
			'page'       => 'single %s',
			'product'    => 'product pages',
			'attachment' => 'media %s',
		];

		$tabs['p_types'] = [
			'title' => esc_html__( 'Post Types:', 'rank-math' ),
			'type'  => 'seprator',
			'name'  => 'p_types_separator',
		];

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$obj             = get_post_type_object( $post_type );
			$link            = isset( $links[ $obj->name ] ) ? $links[ $obj->name ] : '';
			$obj_name        = isset( $names[ $obj->name ] ) ? sprintf( $names[ $obj->name ], $obj->name ) : $obj->name;
			$is_attachment   = $post_type === 'attachment';
			$richsnp_default = 'off';
			if ( ( class_exists( 'WooCommerce' ) && 'product' === $post_type ) || ( class_exists( 'Easy_Digital_Downloads' ) && 'download' === $post_type ) ) {
				$richsnp_default = 'product';
			}
			if ( $post_type === 'post' ) {
				$richsnp_default = 'article';
			}

			$primary_taxonomy_hash = [
				'post'    => 'category',
				'product' => 'product_cat',
			];

			$tabs[ 'post-type-' . $obj->name ] = [
				'title'     => $is_attachment ? esc_html__( 'Attachments', 'rank-math' ) : $obj->label,
				'icon'      => isset( $icons[ $obj->name ] ) ? $icons[ $obj->name ] : $icons['default'],
				/* translators: 1. post type name 2. link */
				'desc'      => sprintf( esc_html__( 'Change Global SEO, Schema, and other settings for %1$s. %2$s', 'rank-math' ), $obj_name, $link ),
				'post_type' => $obj->name,
				'file'      => rank_math()->includes_dir() . 'settings/titles/post-types.php',
				'classes'   => 'attachment' === $post_type ? 'rank-math-advanced-option' : '',
				'json'      => [
					'isWooCommerceActive' => class_exists( 'WooCommerce' ),
					'isEddActive'         => class_exists( 'Easy_Digital_Downloads' ),
					'isWebStoriesActive'  => defined( 'WEBSTORIES_VERSION' ),
					$post_type            => [
						'title'                => $is_attachment ? esc_html__( 'Attachments', 'rank-math' ) : $obj->label,
						'name'                 => $is_attachment ? esc_html__( 'Media', 'rank-math' ) : $obj->labels->singular_name,
						'schemaTypes'          => Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'rank-math' ), $post_type ),
						'taxonomies'           => Helper::get_object_taxonomies( $post_type ),
						'hasArchive'           => $obj->has_archive,
						'customRobots'         => false,
						'schemaDefault'        => $this->do_filter( 'settings/snippet/type', $richsnp_default, $post_type ),
						'articleType'          => $this->do_filter( 'settings/snippet/article_type', 'post' === $post_type ? 'BlogPosting' : 'Article', $post_type ),
						'enableLinkSuggestion' => $this->do_filter( 'settings/titles/link_suggestions', true, $post_type ),
						'primaryTaxonomy'      => isset( $primary_taxonomy_hash[ $post_type ] ) ? $primary_taxonomy_hash[ $post_type ] : 'off',
					],
				],
			];
		}

		return $tabs;
	}

	/**
	 * Add taxonomy tabs in the Title Settings panel.
	 *
	 * @param  array $tabs Holds the tabs of the options panel.
	 * @return array
	 */
	public function title_taxonomy_settings( $tabs ) {
		$icons = Helper::choices_taxonomy_icons();

		$hash_name = [
			'category'    => 'category archive pages',
			'product_cat' => 'Product category pages',
			'product_tag' => 'Product tag pages',
		];

		$hash_link = [
			'category'    => '<a href="' . KB::get( 'category-settings', 'Options Panel Meta Categories Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'post_tag'    => '<a href="' . KB::get( 'tag-settings', 'Options Panel Meta Tags Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'product_cat' => '<a href="' . KB::get( 'product-categories-settings', 'Options Panel Meta Product Categories Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
			'product_tag' => '<a href="' . KB::get( 'product-tags-settings', 'Options Panel Meta Product Tags Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.',
		];

		$taxonomies_data = [];
		foreach ( Helper::get_accessible_taxonomies() as $taxonomy ) {
			$attached = implode( ' + ', $taxonomy->object_type );

			$taxonomies_data[ $attached ][ $taxonomy->name ] = $taxonomy;
		}

		foreach ( $taxonomies_data as $attached => $taxonomies ) {
			// Seprator.
			$tabs[ $attached ] = [
				'title' => ucwords( $attached ) . ':',
				'type'  => 'seprator',
				'name'  => 'taxonomy_separator',
			];

			foreach ( $taxonomies as $taxonomy ) {
				$link          = isset( $hash_link[ $taxonomy->name ] ) ? $hash_link[ $taxonomy->name ] : '';
				$taxonomy_name = isset( $hash_name[ $taxonomy->name ] ) ? $hash_name[ $taxonomy->name ] : $taxonomy->label;

				$tabs[ 'taxonomy-' . $taxonomy->name ] = [
					'icon'     => isset( $icons[ $taxonomy->name ] ) ? $icons[ $taxonomy->name ] : $icons['default'],
					'title'    => $taxonomy->label,
					/* translators: 1. taxonomy name 2. link */
					'desc'     => sprintf( esc_html__( 'Change Global SEO, Schema, and other settings for %1$s. %2$s', 'rank-math' ), $taxonomy_name, $link ),
					'taxonomy' => $taxonomy->name,
					'file'     => rank_math()->includes_dir() . 'settings/titles/taxonomies.php',
				];
			}
		}

		if ( isset( $tabs['taxonomy-post_format'] ) ) {
			$tab = $tabs['taxonomy-post_format'];
			unset( $tabs['taxonomy-post_format'] );
			$tab['title']      = esc_html__( 'Post Formats', 'rank-math' );
			$tab['page_title'] = esc_html__( 'Post Formats Archive', 'rank-math' );
			Arr::insert( $tabs, [ 'taxonomy-post_format' => $tab ], 5 );
		}

		return $tabs;
	}

	/**
	 * Save Settings data. Called from the `updateSettings` endpoint.
	 *
	 * @param string $type        Settings type.
	 * @param array  $settings    Settings data.
	 * @param array  $field_types Field ids with types use to sanitize the value.
	 * @param array  $updated     Array of field ids that were updated.
	 * @param bool   $is_reset    Whether the request is to reset the data.
	 * @return array
	 */
	public static function save_settings( $type, $settings, $field_types, $updated, $is_reset ) {
		$notifications = [];

		$update_htaccess = self::maybe_update_htaccess( $settings );
		if ( ! empty( $update_htaccess ) ) {
			$notifications[] = $update_htaccess;
		}

		$update_analytics = self::maybe_update_analytics( $settings, $updated );
		if ( ! empty( $update_analytics ) ) {
			$notifications[] = $update_analytics;
		}

		do_action( 'rank_math/settings/before_save', $type, $settings );
		foreach (
			[
				'htaccess_allow_editing',
				'htaccess_content',
				'searchConsole',
				'analyticsData',
				'analytics',
				'usage_tracking',
			] as $key
		) {
			if ( isset( $settings[ $key ] ) ) {
				unset( $settings[ $key ] );
			}
		}

		$settings = Sanitize_Settings::sanitize( $settings, $field_types );
		self::check_updated_fields( $updated, $is_reset );

		// Get current settings to compare with new settings.
		$current_settings = Helper::get_settings( $type );

		// Only update fields that have actually changed.
		$changed_settings = self::get_changed_settings( $current_settings, $settings );

		// If no settings have changed, return early.
		if ( empty( $changed_settings ) ) {
			return [
				'notifications' => $notifications,
				'settings'      => $current_settings,
			];
		}

		$map = [
			'general' => [ $changed_settings, null, null ],
			'titles'  => [ null, $changed_settings, null ],
			'sitemap' => [ null, null, $changed_settings ],
		];

		Helper::update_all_settings( ...$map[ $type ] );
		rank_math()->settings->reset();

		do_action( 'rank_math/settings/after_save', $type, $changed_settings );

		return [
			'notifications' => $notifications,
			'settings'      => apply_filters( 'rank_math/settings/saved_data', Helper::get_settings( $type ), $type ),
		];
	}

	/**
	 * Update Analytics data.
	 *
	 * @param array  $settings Settings data.
	 * @param string $updated  View that was updated.
	 */
	public static function maybe_update_analytics( $settings, $updated ) {
		if ( empty( $updated ) || ( ! in_array( 'searchConsole', $updated, true ) && ! in_array( 'analyticsData', $updated, true ) ) ) {
			return;
		}

		Search_Console::save( $settings );

		$days = $settings['console_caching_control'] ?? 90;

		$search_console = $settings['searchConsole'] ?? [];
		if ( in_array( 'searchConsole', $updated, true ) && ! empty( $search_console ) ) {
			$search_console['days'] = $days;

			$response = \RankMath\Analytics\AJAX::get()->do_save_analytic_profile( $search_console );
			if ( is_wp_error( $response ) ) {
				return [
					'error' => $response->get_error_message(),
				];
			}
		}

		$google_analytics = $settings['analyticsData'] ?? [];
		if ( in_array( 'analyticsData', $updated, true ) && ! empty( $google_analytics ) ) {
			$google_analytics['days'] = $days;

			$response = \RankMath\Analytics\AJAX::get()->do_save_analytic_options( $google_analytics );
			if ( is_wp_error( $response ) ) {
				return [
					'error' => $response->get_error_message(),
				];
			}
		}
	}

	/**
	 * Enqueue settings translations when React UI is enabled as the settings pages are loaded in chunks.
	 */
	public function enqueue_settings_translations() {
		if ( ! Helper::is_react_enabled() ) {
			return;
		}

		$page = str_replace( 'rank-math-options-', '', Param::get( 'page', '' ) );
		$hash = [
			'general'          => 'generalSettings',
			'titles'           => 'titleSettings',
			'sitemap'          => 'sitemapSettings',
			'instant-indexing' => 'instantIndexingSettings',
		];

		if ( ! isset( $hash[ $page ] ) ) {
			return;
		}

		$chunk = $hash[ $page ];
		wp_enqueue_script( 'rank-math-settings-chunk', rank_math()->plugin_url() . "assets/admin/js/$chunk.js", [ 'rank-math-options' ], rank_math()->version, true );
		wp_set_script_translations( 'rank-math-settings-chunk', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
		wp_set_script_translations( 'rank-math-options', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
		wp_set_script_translations( 'rank-math-components', 'rank-math', rank_math()->plugin_dir() . 'languages/' );
	}

	/**
	 * Check if certain fields got updated.
	 *
	 * @param array $updated  Updated fields id.
	 * @param bool  $is_reset Whether to reset the settings.
	 */
	private static function check_updated_fields( $updated, $is_reset ) {
		if ( $is_reset ) {
			Helper::schedule_flush_rewrite();
			return;
		}

		/**
		 * Filter: Allow developers to add option fields which will flush the rewrite rules when updated.
		 *
		 * @param array $flush_fields Array of field IDs for which we need to flush.
		 */
		$flush_fields = apply_filters(
			'rank_math/flush_fields',
			[
				'strip_category_base',
				'disable_author_archives',
				'url_author_base',
				'attachment_redirect_urls',
				'attachment_redirect_default',
				'nofollow_external_links',
				'nofollow_image_links',
				'nofollow_domains',
				'nofollow_exclude_domains',
				'new_window_external_links',
				'redirections_header_code',
				'redirections_post_redirect',
				'redirections_debug',
			]
		);

		foreach ( $flush_fields as $field_id ) {
			if ( in_array( $field_id, $updated, true ) ) {
				Helper::schedule_flush_rewrite();
				break;
			}
		}
	}

	/**
	 * Get only the settings that have changed.
	 *
	 * @param array $current_settings Current settings from database.
	 * @param array $new_settings     New settings to be saved.
	 * @return array
	 */
	private static function get_changed_settings( $current_settings, $new_settings ) {
		// Filter out invalid keys from both arrays.
		$new_settings     = array_filter( $new_settings, [ __CLASS__, 'is_valid_key' ], ARRAY_FILTER_USE_KEY );
		$current_settings = array_filter( $current_settings, [ __CLASS__, 'is_valid_key' ], ARRAY_FILTER_USE_KEY );

		// Merge current settings with new settings, new settings take precedence.
		return array_merge( $current_settings, $new_settings );
	}

	/**
	 * Check if a key is valid for settings.
	 *
	 * @param mixed $key The key to validate.
	 * @return bool
	 */
	private static function is_valid_key( $key ) {
		return is_string( $key ) && ! empty( $key );
	}

	/**
	 * Update .htaccess.
	 *
	 * @param array { $settings } Settings data.
	 */
	private static function maybe_update_htaccess( $settings ) {
		if ( empty( $settings['htaccess_allow_editing'] ) ) {
			return;
		}

		if ( ! is_super_admin() || ! Helper::has_cap( 'general' ) || ! Helper::has_cap( 'edit_htaccess' ) ) {
			return [
				'error' => esc_html__( 'You do not have permission to edit the .htaccess file.', 'rank-math' ),
			];
		}

		if ( ! Helper::is_edit_allowed() ) {
			return [
				'error' => esc_html__( 'You do not have permission to edit the .htaccess file.', 'rank-math' ),
			];
		}

		// phpcs:ignore= WordPress.Security.ValidatedSanitizedInput -- Writing to .htaccess file and escaping for HTML will break functionality.
		$content = isset( $settings['htaccess_content'] ) ? $settings['htaccess_content'] : '';
		if ( empty( $content ) ) {
			return;
		}

		if ( ! self::do_htaccess_backup() ) {
			return [
				'error' => esc_html__( 'Failed to backup .htaccess file. Please check file permissions.', 'rank-math' ),
			];
		}
		if ( ! self::do_htaccess_update( $content ) ) {
			return [
				'error' => esc_html__( 'Failed to update .htaccess file. Please check file permissions.', 'rank-math' ),
			];
		}

		return [
			'success' => esc_html__( '.htaccess file updated successfully.', 'rank-math' ),
		];
	}

	/**
	 * Create htaccess backup.
	 *
	 * @return bool
	 */
	private static function do_htaccess_backup() {
		if ( ! Helper::is_filesystem_direct() ) {
			return false;
		}

		$wp_filesystem = Helper::get_filesystem();

		$path = get_home_path();
		$file = $path . '.htaccess';
		if ( ! $wp_filesystem->is_writable( $path ) || ! $wp_filesystem->exists( $file ) ) {
			return false;
		}

		$backup = $path . uniqid( '.htaccess_back_' );
		return $wp_filesystem->copy( $file, $backup, true );
	}

	/**
	 * Update htaccess file.
	 *
	 * @param string $content Htaccess content.
	 * @return string|bool
	 */
	private static function do_htaccess_update( $content ) {
		if ( empty( $content ) || ! Helper::is_filesystem_direct() ) {
			return false;
		}

		$wp_filesystem = Helper::get_filesystem();
		$htaccess_file = get_home_path() . '.htaccess';

		return ! $wp_filesystem->is_writable( $htaccess_file ) ? false : $wp_filesystem->put_contents( $htaccess_file, $content );
	}
}
