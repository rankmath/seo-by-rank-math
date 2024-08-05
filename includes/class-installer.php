<?php
/**
 * Plugin activation and deactivation functionality.
 *
 * This class defines all code necessary to run during the plugin's activation.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath;

use RankMath\Traits\Hooker;
use RankMath\Admin\Watcher;
use RankMath\Helper;
use RankMath\Admin\Admin_Helper;
use RankMath\Role_Manager\Capability_Manager;

defined( 'ABSPATH' ) || exit;

/**
 * Installer class.
 */
class Installer {

	use Hooker;

	/**
	 * Bind all events.
	 */
	public function __construct() {
		register_activation_hook( RANK_MATH_FILE, [ $this, 'activation' ] );
		register_deactivation_hook( RANK_MATH_FILE, [ $this, 'deactivation' ] );

		$this->action( 'wp', 'create_cron_jobs' );
		$this->action( 'wp_initialize_site', 'initialize_site' );
		$this->filter( 'wpmu_drop_tables', 'on_delete_blog' );
	}

	/**
	 * Do things when activating Rank Math.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function activation( $network_wide = false ) {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->activate();
			return;
		}

		$this->network_activate_deactivate( true );
	}

	/**
	 * Do things when deactivating Rank Math.
	 *
	 * @param bool $network_wide Whether the plugin is being activated network-wide.
	 */
	public function deactivation( $network_wide = false ) {
		if ( ! is_multisite() || ! $network_wide ) {
			$this->deactivate();
			return;
		}

		$this->network_activate_deactivate( false );
	}

	/**
	 * Fired when a new site is activated with a WPMU environment.
	 *
	 * @param WP_Site $site The new site's object.
	 */
	public function initialize_site( $site ) {
		switch_to_blog( $site->blog_id );
		$this->activate();
		restore_current_blog();
	}

	/**
	 * Uninstall tables when MU blog is deleted.
	 *
	 * @param  array $tables List of tables that will be deleted by WP.
	 * @return array
	 */
	public function on_delete_blog( $tables ) {
		global $wpdb;

		$tables[] = $wpdb->prefix . 'rank_math_404_logs';
		$tables[] = $wpdb->prefix . 'rank_math_redirections';
		$tables[] = $wpdb->prefix . 'rank_math_redirections_cache';
		$tables[] = $wpdb->prefix . 'rank_math_internal_links';
		$tables[] = $wpdb->prefix . 'rank_math_internal_meta';
		$tables[] = $wpdb->prefix . 'rank_math_analytics_gsc';
		$tables[] = $wpdb->prefix . 'rank_math_analytics_objects';
		$tables[] = $wpdb->prefix . 'rank_math_analytics_inspections';

		return $tables;
	}

	/**
	 * Run network-wide activation/deactivation of the plugin.
	 *
	 * @param bool $activate True for plugin activation, false for de-activation.
	 *
	 * @copyright Copyright (C) 2008-2019, Yoast BV
	 * The following code is a derivative work of the code from the Yoast(https://github.com/Yoast/wordpress-seo/), which is licensed under GPL v3.
	 */
	private function network_activate_deactivate( $activate ) {
		global $wpdb;

		$blog_ids = $wpdb->get_col( "SELECT blog_id FROM $wpdb->blogs WHERE archived = '0' AND spam = '0' AND deleted = '0'" );
		if ( empty( $blog_ids ) ) {
			return;
		}

		foreach ( $blog_ids as $blog_id ) {
			$func = true === $activate ? 'activate' : 'deactivate';

			switch_to_blog( $blog_id );
			$this->$func();
			restore_current_blog();
		}
	}

	/**
	 * Plugin activation callback.
	 */
	private function activate() {
		// Init to use the common filters.
		new \RankMath\Defaults();

		$current_version    = get_option( 'rank_math_version', null );
		$current_db_version = get_option( 'rank_math_db_version', null );

		$this->create_options();
		$this->set_capabilities();
		$this->create_cron_jobs();

		if ( is_null( $current_version ) && is_null( $current_db_version ) ) {
			set_transient( '_rank_math_activation_redirect', 1, 30 );
		}

		// Update to latest version.
		update_option( 'rank_math_version', rank_math()->version );
		update_option( 'rank_math_db_version', rank_math()->db_version );

		// Clear rollback option if necessary.
		if ( rank_math()->version !== get_option( 'rank_math_rollback_version' ) ) {
			delete_option( 'rank_math_rollback_version' );
		}

		// Save install date.
		if ( false === boolval( get_option( 'rank_math_install_date' ) ) ) {
			update_option( 'rank_math_install_date', current_time( 'timestamp' ) ); // phpcs:ignore
		}

		// Activate Watcher.
		$watcher = new Watcher();
		$watcher->check_activated_plugin();
		$watcher->check_search_engine_visibility( ! get_option( 'blog_public' ) );

		$this->clear_rewrite_rules( true );
		Helper::clear_cache( 'activate' );

		$this->do_action( 'activate' );
	}

	/**
	 * Runs on deactivation of the plugin.
	 */
	private function deactivate() {
		$this->clear_rewrite_rules( false );
		$this->remove_cron_jobs();
		Helper::clear_cache( 'deactivate' );
		Admin_Helper::deregister_user();
		$this->do_action( 'deactivate' );
	}

	/**
	 * Set up the database tables.
	 *
	 * @param mixed $modules Modules to create tables for.
	 * @return void
	 */
	public static function create_tables( $modules = [] ) {
		global $wpdb;

		$collate      = $wpdb->get_charset_collate();
		$table_schema = [];

		if ( in_array( '404-monitor', $modules, true ) ) {
			$table_schema[] = "CREATE TABLE {$wpdb->prefix}rank_math_404_logs (
				id bigint(20) unsigned NOT NULL auto_increment,
				uri varchar(255) NOT NULL,
				accessed datetime NOT NULL default '0000-00-00 00:00:00',
				times_accessed bigint(20) unsigned NOT NULL default 1,
				referer varchar(255) NOT NULL default '',
				user_agent varchar(255) NOT NULL default '',
				PRIMARY KEY  (id),
				KEY uri (uri(191))
			) $collate;";
		}

		if ( in_array( 'redirections', $modules, true ) ) {
			$table_schema[] = "CREATE TABLE {$wpdb->prefix}rank_math_redirections (
				id bigint(20) unsigned NOT NULL auto_increment,
				sources text CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->charset}_bin NOT NULL,
				url_to text NOT NULL,
				header_code smallint(4) unsigned NOT NULL,
				hits bigint(20) unsigned NOT NULL default '0',
				status varchar(25) NOT NULL default 'active',
				created datetime NOT NULL default '0000-00-00 00:00:00',
				updated datetime NOT NULL default '0000-00-00 00:00:00',
				last_accessed datetime NOT NULL default '0000-00-00 00:00:00',
				PRIMARY KEY  (id),
				KEY status (status)
			) $collate;";

			$table_schema[] = "CREATE TABLE {$wpdb->prefix}rank_math_redirections_cache (
				id bigint(20) unsigned NOT NULL auto_increment,
				from_url text CHARACTER SET {$wpdb->charset} COLLATE {$wpdb->charset}_bin NOT NULL,
				redirection_id bigint(20) unsigned NOT NULL,
				object_id bigint(20) unsigned NOT NULL default '0',
				object_type varchar(10) NOT NULL default 'post',
				is_redirected tinyint(1) NOT NULL default '0',
				PRIMARY KEY  (id),
				KEY redirection_id (redirection_id)
			) $collate;";
		}

		if ( in_array( 'link-counter', $modules, true ) ) {
			$table_schema[] = "CREATE TABLE {$wpdb->prefix}rank_math_internal_links (
				id bigint(20) unsigned NOT NULL auto_increment,
				url varchar(255) NOT NULL,
				post_id bigint(20) unsigned NOT NULL,
				target_post_id bigint(20) unsigned NOT NULL,
				type varchar(8) NOT NULL,
				PRIMARY KEY  (id),
				KEY link_direction (post_id, type)
			) $collate;";

			$table_schema[] = "CREATE TABLE {$wpdb->prefix}rank_math_internal_meta (
				object_id bigint(20) unsigned NOT NULL,
				internal_link_count int(10) unsigned NULL default 0,
				external_link_count int(10) unsigned NULL default 0,
				incoming_link_count int(10) unsigned NULL default 0,
				PRIMARY KEY  (object_id)
			) $collate;";
		}

		$table_schema = apply_filters( 'rank_math/admin/create_tables', $table_schema, $modules );

		require_once ABSPATH . 'wp-admin/includes/upgrade.php';
		foreach ( $table_schema as $table ) {
			dbDelta( $table );
		}
	}

	/**
	 * Create options.
	 */
	private function create_options() {
		$this->create_misc_options();
		$this->create_general_options();
		$this->create_titles_sitemaps_options();
		$this->create_instant_indexing_options();
	}

	/**
	 * Create misc options.
	 */
	private function create_misc_options() {
		// Update "known CPTs" list, so we can send notice about new ones later.
		add_option( 'rank_math_known_post_types', Helper::get_accessible_post_types() );

		$modules = [
			'link-counter',
			'analytics',
			'seo-analysis',
			'sitemap',
			'rich-snippet',
			'woocommerce',
			'buddypress',
			'bbpress',
			'acf',
			'web-stories',
			'content-ai',
			'instant-indexing',
		];

		// Role Manager.
		$users = get_users( [ 'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ] ] );
		if ( count( $users ) > 1 ) {
			$modules[] = 'role-manager';
		}

		// If AMP plugin is installed.
		if ( function_exists( 'is_amp_endpoint' ) || class_exists( 'Better_AMP' ) || class_exists( 'Weeblramp_Api' ) || class_exists( 'AMPHTML' ) ) {
			$modules[] = 'amp';
		}

		// If 404-monitor is active as plugin.
		if ( false !== get_option( 'rank_math_monitor_version', false ) ) {
			$modules[] = '404-monitor';
		}

		add_option( 'rank_math_modules', $modules );
		self::create_tables( $modules );
	}

	/**
	 * Add defaults for general options.
	 */
	private function create_general_options() {
		$post_types = Helper::get_accessible_post_types();
		if ( isset( $post_types['attachment'] ) ) {
			unset( $post_types['attachment'] );
		}

		add_option(
			'rank-math-options-general',
			$this->do_filter(
				'settings/defaults/general',
				[
					'strip_category_base'                 => 'off',
					'attachment_redirect_urls'            => 'on',
					'attachment_redirect_default'         => get_home_url(),
					'nofollow_external_links'             => 'off',
					'nofollow_image_links'                => 'off',
					'new_window_external_links'           => 'on',
					'add_img_alt'                         => 'off',
					'img_alt_format'                      => ' %filename%',
					'add_img_title'                       => 'off',
					'img_title_format'                    => '%title% %count(title)%',
					'breadcrumbs'                         => 'off',
					'breadcrumbs_separator'               => '-',
					'breadcrumbs_home'                    => 'on',
					'breadcrumbs_home_label'              => esc_html__( 'Home', 'rank-math' ),
					/* translators: Archive title */
					'breadcrumbs_archive_format'          => esc_html__( 'Archives for %s', 'rank-math' ),
					/* translators: Search query term */
					'breadcrumbs_search_format'           => esc_html__( 'Results for %s', 'rank-math' ),
					'breadcrumbs_404_label'               => esc_html__( '404 Error: page not found', 'rank-math' ),
					'breadcrumbs_ancestor_categories'     => 'off',
					'breadcrumbs_blog_page'               => 'off',
					'404_monitor_mode'                    => 'simple',
					'404_monitor_limit'                   => 100,
					'404_monitor_ignore_query_parameters' => 'on',
					'redirections_header_code'            => '301',
					'redirections_debug'                  => 'off',
					'console_caching_control'             => '90',
					'console_email_reports'               => 'on',
					'console_email_frequency'             => 'monthly',
					'link_builder_links_per_page'         => '7',
					'link_builder_links_per_target'       => '1',
					'wc_remove_product_base'              => 'off',
					'wc_remove_category_base'             => 'off',
					'wc_remove_category_parent_slugs'     => 'off',
					'rss_before_content'                  => '',
					'rss_after_content'                   => '',
					'wc_remove_generator'                 => 'on',
					'remove_shop_snippet_data'            => 'on',
					'frontend_seo_score'                  => 'off',
					'frontend_seo_score_post_types'       => [ 'post' ],
					'frontend_seo_score_position'         => 'top',
					'setup_mode'                          => 'advanced',
					'content_ai_post_types'               => array_keys( $post_types ),
					'content_ai_country'                  => 'all',
					'content_ai_tone'                     => 'Formal',
					'content_ai_audience'                 => 'General Audience',
					'content_ai_language'                 => Helper::content_ai_default_language(),
					'analytics_stats'                     => 'on',
					'toc_block_title'                     => 'Table of Contents',
					'toc_block_list_style'                => 'ul',
				]
			)
		);
	}

	/**
	 * Add default values.
	 */
	private function create_titles_sitemaps_options() {
		$sitemap = [
			'items_per_page'          => 200,
			'include_images'          => 'on',
			'include_featured_image'  => 'off',
			'exclude_roles'           => $this->get_excluded_roles(),
			'html_sitemap'            => 'on',
			'html_sitemap_display'    => 'shortcode',
			'html_sitemap_sort'       => 'published',
			'html_sitemap_seo_titles' => 'titles',
			'authors_sitemap'         => 'on',
		];
		$titles  = [
			'noindex_empty_taxonomies'   => 'on',
			'title_separator'            => '-',
			'capitalize_titles'          => 'off',
			'twitter_card_type'          => 'summary_large_image',
			'knowledgegraph_type'        => class_exists( 'Easy_Digital_Downloads' ) || class_exists( 'WooCommerce' ) ? 'company' : 'person',
			'knowledgegraph_name'        => get_bloginfo( 'name' ),
			'website_name'               => get_bloginfo( 'name' ),
			'local_business_type'        => 'Organization',
			'local_address_format'       => '{address} {locality}, {region} {postalcode}',
			'opening_hours'              => $this->get_opening_hours(),
			'opening_hours_format'       => 'off',
			'homepage_title'             => '%sitename% %page% %sep% %sitedesc%',
			'homepage_description'       => '',
			'homepage_custom_robots'     => 'off',
			'disable_author_archives'    => 'off',
			'url_author_base'            => 'author',
			'author_custom_robots'       => 'on',
			'author_robots'              => [ 'noindex' ],
			'author_archive_title'       => '%name% %sep% %sitename% %page%',
			'author_add_meta_box'        => 'on',
			'disable_date_archives'      => 'on',
			'date_archive_title'         => '%date% %page% %sep% %sitename%',
			'search_title'               => '%search_query% %page% %sep% %sitename%',
			'404_title'                  => 'Page Not Found %sep% %sitename%',
			'date_archive_robots'        => [ 'noindex' ],
			'noindex_search'             => 'on',
			'noindex_archive_subpages'   => 'off',
			'noindex_password_protected' => 'off',
		];

		$this->create_post_type_options( $titles, $sitemap );
		$this->create_taxonomy_options( $titles, $sitemap );

		add_option( 'rank-math-options-titles', $this->do_filter( 'settings/defaults/titles', $titles ) );
		add_option( 'rank-math-options-sitemap', $this->do_filter( 'settings/defaults/sitemap', $sitemap ) );
	}

	/**
	 * Create post type options.
	 *
	 * @param array $titles  Hold title settings.
	 * @param array $sitemap Hold sitemap settings.
	 */
	private function create_post_type_options( &$titles, &$sitemap ) {
		$post_types = Helper::get_accessible_post_types();
		array_push( $post_types, 'product', 'web-story' );

		$titles['pt_download_default_rich_snippet'] = 'product';
		$titles['author_slack_enhanced_sharing']    = 'on';

		foreach ( $post_types as $post_type ) {
			$defaults = $this->get_post_type_defaults( $post_type );

			$titles[ 'pt_' . $post_type . '_title' ]                = '%title% %sep% %sitename%';
			$titles[ 'pt_' . $post_type . '_description' ]          = '%excerpt%';
			$titles[ 'pt_' . $post_type . '_robots' ]               = $defaults['robots'];
			$titles[ 'pt_' . $post_type . '_custom_robots' ]        = $defaults['is_custom'];
			$titles[ 'pt_' . $post_type . '_default_rich_snippet' ] = $defaults['rich_snippet'];
			$titles[ 'pt_' . $post_type . '_default_article_type' ] = $defaults['article_type'];
			$titles[ 'pt_' . $post_type . '_default_snippet_name' ] = '%seo_title%';
			$titles[ 'pt_' . $post_type . '_default_snippet_desc' ] = '%seo_description%';

			if ( $this->has_archive( $post_type ) ) {
				$titles[ 'pt_' . $post_type . '_archive_title' ] = '%title% %page% %sep% %sitename%';
			}

			// Slack enhanced sharing is off by default, except for posts, pages, products, and downloads.
			$titles[ 'pt_' . $post_type . '_slack_enhanced_sharing' ] = 'off';
			if ( in_array( $post_type, [ 'post', 'page', 'product', 'download' ], true ) ) {
				$titles[ 'pt_' . $post_type . '_slack_enhanced_sharing' ] = 'on';
			}

			if ( in_array( $post_type, [ 'attachment', 'web-story' ], true ) ) {
				$sitemap[ 'pt_' . $post_type . '_sitemap' ]     = 'off';
				$titles[ 'pt_' . $post_type . '_add_meta_box' ] = 'off';
				continue;
			}

			$sitemap[ 'pt_' . $post_type . '_sitemap' ]         = 'on';
			$titles[ 'pt_' . $post_type . '_ls_use_fk' ]        = 'titles';
			$titles[ 'pt_' . $post_type . '_add_meta_box' ]     = 'on';
			$titles[ 'pt_' . $post_type . '_bulk_editing' ]     = 'editing';
			$titles[ 'pt_' . $post_type . '_link_suggestions' ] = 'on';

			// Primary Taxonomy.
			$taxonomy_hash = [
				'post'    => 'category',
				'product' => 'product_cat',
			];

			if ( isset( $taxonomy_hash[ $post_type ] ) ) {
				$titles[ 'pt_' . $post_type . '_primary_taxonomy' ] = $taxonomy_hash[ $post_type ];
			}
		}
	}

	/**
	 * Get robots default for post type.
	 *
	 * @param  string $post_type Post type.
	 * @return array
	 */
	private function get_post_type_defaults( $post_type ) {
		$rich_snippets = [
			'post'      => 'article',
			'page'      => 'article',
			'product'   => 'product',
			'download'  => 'product',
			'web-story' => 'article',
		];

		$defaults = [
			'robots'       => [],
			'is_custom'    => 'off',
			'rich_snippet' => isset( $rich_snippets[ $post_type ] ) ? $rich_snippets[ $post_type ] : 'off',
			'article_type' => 'post' === $post_type ? 'BlogPosting' : 'Article',
		];

		if ( 'attachment' === $post_type ) {
			$defaults['is_custom'] = 'on';
			$defaults['robots']    = [ 'noindex' ];
		}

		return $defaults;
	}

	/**
	 * Check post type has archive.
	 *
	 * @param  string $post_type Post type.
	 * @return bool
	 */
	private function has_archive( $post_type ) {
		$post_type_obj = get_post_type_object( $post_type );
		return ! is_null( $post_type_obj ) && $post_type_obj->has_archive;
	}

	/**
	 * Create post type options.
	 *
	 * @param array $titles  Hold title settings.
	 * @param array $sitemap Hold sitemap settings.
	 */
	private function create_taxonomy_options( &$titles, &$sitemap ) {
		$taxonomies = Helper::get_accessible_taxonomies();
		foreach ( $taxonomies as $taxonomy => $object ) {
			$defaults = $this->get_taxonomy_defaults( $taxonomy );

			$titles[ 'tax_' . $taxonomy . '_title' ]                  = '%term% %sep% %sitename%';
			$titles[ 'tax_' . $taxonomy . '_robots' ]                 = $defaults['robots'];
			$titles[ 'tax_' . $taxonomy . '_add_meta_box' ]           = $defaults['metabox'];
			$titles[ 'tax_' . $taxonomy . '_custom_robots' ]          = $defaults['is_custom'];
			$titles[ 'tax_' . $taxonomy . '_description' ]            = '%term_description%';
			$titles[ 'tax_' . $taxonomy . '_slack_enhanced_sharing' ] = 'on';

			$sitemap[ 'tax_' . $taxonomy . '_sitemap' ] = 'category' === $taxonomy ? 'on' : 'off';

			if ( substr( $taxonomy, 0, 3 ) === 'pa_' ) {
				$titles[ 'remove_' . $taxonomy . '_snippet_data' ] = 'on';
			}
		}

		$titles['remove_product_cat_snippet_data'] = 'on';
		$titles['remove_product_tag_snippet_data'] = 'on';

	}

	/**
	 * Get robots default for post type.
	 *
	 * @param  string $taxonomy Taxonomy.
	 * @return array
	 */
	private function get_taxonomy_defaults( $taxonomy ) {
		$defaults = [
			'robots'    => [],
			'is_custom' => 'off',
			'metabox'   => 'category' === $taxonomy ? 'on' : 'off',
		];

		if ( in_array( $taxonomy, [ 'post_tag', 'post_format', 'product_tag' ], true ) ) {
			$defaults['is_custom'] = 'on';
			$defaults['robots']    = [ 'noindex' ];
		}

		return $defaults;
	}

	/**
	 * Create capabilities.
	 */
	private function set_capabilities() {
		$admin = get_role( 'administrator' );
		if ( ! is_null( $admin ) ) {
			$admin->add_cap( 'rank_math_edit_htaccess', true );
		}

		Capability_Manager::get()->create_capabilities();
	}

	/**
	 * Create cron jobs.
	 */
	public function create_cron_jobs() {
		$midnight = strtotime( 'tomorrow midnight' );
		foreach ( $this->get_cron_jobs() as $job => $recurrence ) {
			if ( ! wp_next_scheduled( "rank_math/{$job}" ) ) {
				$timestamp = 'content-ai/update_prompts' === $job ? $midnight + wp_rand( 60, 86400 ) : $midnight;
				wp_schedule_event( $timestamp, $this->do_filter( "{$job}_recurrence", $recurrence ), "rank_math/{$job}" );
			}
		}
	}

	/**
	 * Remove cron jobs.
	 */
	private function remove_cron_jobs() {
		foreach ( $this->get_cron_jobs() as $job => $recurrence ) {
			wp_clear_scheduled_hook( "rank_math/{$job}" );
		}
	}

	/**
	 * Get cron jobs.
	 *
	 * @return array
	 */
	private function get_cron_jobs() {
		return [
			'redirection/clean_trashed' => 'daily', // Add cron for cleaning trashed redirects.
			'links/internal_links'      => 'daily', // Add cron for counting links.
			'content-ai/update_prompts' => 'daily', // Add cron for updating the prompts data.
		];
	}

	/**
	 * Get opening hours.
	 *
	 * @return array
	 */
	private function get_opening_hours() {
		$hours = [];
		$days  = [ 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday' ];
		foreach ( $days as $day ) {
			$hours[] = [
				'day'  => $day,
				'time' => '09:00-17:00',
			];
		}

		return $hours;
	}

	/**
	 * Get roles to exclude.
	 *
	 * @return array
	 */
	private function get_excluded_roles() {
		$roles = Helper::get_roles();
		unset( $roles['administrator'], $roles['editor'], $roles['author'] );

		return array_keys( $roles );
	}

	/**
	 * Clear rewrite rules.
	 *
	 * @param bool $activate True for plugin activation, false for de-activation.
	 */
	private function clear_rewrite_rules( $activate ) {
		if ( is_multisite() && ms_is_switched() ) {
			delete_option( 'rewrite_rules' );
			Helper::schedule_flush_rewrite();
			return;
		}

		// On activation.
		if ( $activate ) {
			Helper::schedule_flush_rewrite();
			return;
		}

		// On deactivation.
		add_action( 'shutdown', 'flush_rewrite_rules' );
	}

	/**
	 * Add defaults for the Instant Indexing module options.
	 *
	 * @return void
	 */
	private function create_instant_indexing_options() {
		add_option(
			'rank-math-options-instant-indexing',
			$this->do_filter(
				'settings/defaults/instant-indexing',
				[
					'bing_post_types' => [ 'post', 'page' ],
				]
			)
		);
	}

}
