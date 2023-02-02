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
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Param;
use MyThemeShop\Helpers\WordPress;

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

		// Check for fields and act accordingly.
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-general_options', 'check_updated_fields', 25, 2 );
		$this->action( 'cmb2_save_options-page_fields_rank-math-options-titles_options', 'check_updated_fields', 25, 2 );
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

		new Options(
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
		$tabs = [
			'global'   => [
				'icon'  => 'rm-icon rm-icon-settings',
				'title' => esc_html__( 'Global Meta', 'rank-math' ),
				/* translators: Link to KB article */
				'desc'  => sprintf( esc_html__( 'Change Global meta settings that take effect across your website. %s.', 'rank-math' ), '<a href="' . KB::get( 'titles-meta', 'Options Panel Meta Global Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
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
			],
			'author'   => [
				'icon'  => 'rm-icon rm-icon-users',
				'title' => esc_html__( 'Authors', 'rank-math' ),
				/* translators: Link to KB article */
				'desc'  => sprintf( esc_html__( 'Change SEO options related to the author archives. %s.', 'rank-math' ), '<a href="' . KB::get( 'author-settings', 'Options Panel Meta Author Tab' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>' ),
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

		new Options(
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
		];

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			$obj      = get_post_type_object( $post_type );
			$link     = isset( $links[ $obj->name ] ) ? $links[ $obj->name ] : '';
			$obj_name = isset( $names[ $obj->name ] ) ? sprintf( $names[ $obj->name ], $obj->name ) : $obj->name;

			$tabs[ 'post-type-' . $obj->name ] = [
				'title'     => 'attachment' === $post_type ? esc_html__( 'Attachments', 'rank-math' ) : $obj->label,
				'icon'      => isset( $icons[ $obj->name ] ) ? $icons[ $obj->name ] : $icons['default'],
				/* translators: 1. post type name 2. link */
				'desc'      => sprintf( esc_html__( 'Change Global SEO, Schema, and other settings for %1$s. %2$s', 'rank-math' ), $obj_name, $link ),
				'post_type' => $obj->name,
				'file'      => rank_math()->includes_dir() . 'settings/titles/post-types.php',
				'classes'   => 'attachment' === $post_type ? 'rank-math-advanced-option' : '',
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
	 * Check if certain fields got updated.
	 *
	 * @param int   $object_id The ID of the current object.
	 * @param array $updated   Array of field ids that were updated.
	 *                         Will only include field ids that had values change.
	 */
	public function check_updated_fields( $object_id, $updated ) {

		/**
		 * Filter: Allow developers to add option fields which will flush the rewrite rules when updated.
		 *
		 * @param array $flush_fields Array of field IDs for which we need to flush.
		 */
		$flush_fields = $this->do_filter(
			'flush_fields',
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

		$this->update_htaccess();
	}

	/**
	 * Update .htaccess.
	 */
	private function update_htaccess() {
		if ( empty( Param::post( 'htaccess_accept_changes' ) ) ) {
			return;
		}

		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- Writing to .htaccess file and escaping for HTML will break functionality.
		$content = wp_unslash( $_POST['htaccess_content'] );
		if ( empty( $content ) ) {
			return;
		}

		if ( ! $this->do_htaccess_backup() ) {
			Helper::add_notification(
				esc_html__( 'Failed to backup .htaccess file. Please check file permissions.', 'rank-math' ),
				[ 'type' => 'error' ]
			);
			return;
		}
		if ( ! $this->do_htaccess_update( $content ) ) {
			Helper::add_notification(
				esc_html__( 'Failed to update .htaccess file. Please check file permissions.', 'rank-math' ),
				[ 'type' => 'error' ]
			);
			return;
		}

		Helper::add_notification( esc_html__( '.htaccess file updated successfully.', 'rank-math' ) );
	}

	/**
	 * Create htaccess backup.
	 *
	 * @return bool
	 */
	private function do_htaccess_backup() {
		if ( ! Helper::is_filesystem_direct() ) {
			return false;
		}

		$wp_filesystem = WordPress::get_filesystem();

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
	private function do_htaccess_update( $content ) {
		if ( empty( $content ) || ! Helper::is_filesystem_direct() ) {
			return false;
		}

		$wp_filesystem = WordPress::get_filesystem();
		$htaccess_file = get_home_path() . '.htaccess';

		return ! $wp_filesystem->is_writable( $htaccess_file ) ? false : $wp_filesystem->put_contents( $htaccess_file, $content );
	}
}
