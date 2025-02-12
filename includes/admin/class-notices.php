<?php
/**
 * The admin notices.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use RankMath\Helpers\Sitepress;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Notices class.
 */
class Notices implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_init', 'notices' );
		$this->action( 'wp_helpers_notification_dismissed', 'notice_dismissible' );
	}

	/**
	 * Run all notices routine.
	 */
	public function notices() {
		$this->is_plugin_configured();
		$this->new_post_type();
		$this->convert_wpml_settings();
		$this->permalink_changes_warning();
	}

	/**
	 * Set known post type after notice dismissal.
	 *
	 * @param string $notification_id Notification id.
	 */
	public function notice_dismissible( $notification_id ) {
		if ( 'new_post_type' === $notification_id ) {
			$current = get_post_types( [ 'public' => true ] );
			update_option( 'rank_math_known_post_types', $current );

			if ( Helper::is_module_active( 'sitemap' ) ) {
				\RankMath\Sitemap\Cache::invalidate_storage();
			}
			return;
		}

		if ( 'convert_wpml_settings' === $notification_id ) {
			update_option( 'rank_math_wpml_notice_dismissed', true );
		}

		if ( 'rank-math-site-url-mismatch' === $notification_id ) {
			update_option( 'rank_math_siteurl_mismatch_notice_dismissed', true );
		}
	}

	/**
	 * If plugin configuration not done.
	 */
	private function is_plugin_configured() {
		if ( 'mts-install-plugins' === Param::get( 'page' ) ) {
			return;
		}

		if ( rank_math()->notification->get_notification_by_id( 'plugin_not_setup' ) && ! Helper::is_configured() ) {
			$message = sprintf(
				'<b>Warning!</b> You didn\'t set up your Rank Math SEO plugin yet, which means you\'re missing out on essential settings and tweaks! <a href="%s">Complete your setup by clicking here.</a>',
				Helper::get_admin_url( 'wizard' )
			);
			Helper::add_notification(
				$message,
				[
					'type' => 'warning',
					'id'   => 'plugin_not_setup',
				]
			);
		}
	}

	/**
	 * Add notification if a new post type is detected.
	 */
	private function new_post_type() {
		$known   = get_option( 'rank_math_known_post_types', [] );
		$current = Helper::get_accessible_post_types();
		$new     = array_diff( $current, $known );

		if ( empty( $new ) ) {
			return;
		}

		$list = '<code>' . implode( '</code>, <code>', $new ) . '</code>';
		/* Translators: placeholder is the post type name. */
		$message = __( 'Rank Math has detected a new post type: %1$s. You may want to check the settings of the <a href="%2$s">Titles &amp; Meta page</a>.', 'rank-math' );
		$count   = count( $new );
		if ( $count > 1 ) {
			/* Translators: placeholder is the post type names separated with commas. */
			$message = __( 'Rank Math has detected new post types: %1$s. You may want to check the settings of the <a href="%2$s">Titles &amp; Meta page</a>.', 'rank-math' );
		}

		$message = $this->do_filter( 'admin/notice/new_post_type', $message, $count );
		$message = sprintf( wp_kses_post( $message ), $list, Helper::get_admin_url( 'options-titles#setting-panel-post-type-' . key( $new ) ), Helper::get_admin_url( 'options-sitemap#setting-panel-sitemap-post-type-' . key( $new ) ) );
		Helper::add_notification(
			$message,
			[
				'type' => 'info',
				'id'   => 'new_post_type',
			]
		);
	}

	/**
	 * Function to show Show String Translation plugin notice and convert the settings.
	 */
	private function convert_wpml_settings() {
		if ( ! Sitepress::get()->is_active() || get_option( 'rank_math_wpml_data_converted' ) ) {
			return;
		}

		if ( ! function_exists( 'icl_add_string_translation' ) ) {
			if ( ! get_option( 'rank_math_wpml_notice_dismissed' ) ) {
				Helper::add_notification(
					__( 'Please activate the WPML String Translation plugin to convert Rank Math Setting values in different languages.', 'rank-math' ),
					[
						'type' => 'error',
						'id'   => 'convert_wpml_settings',
					]
				);
			}
			return;
		}

		$languages = icl_get_languages(); // @phpstan-ignore-line
		foreach ( $languages as $lang_code => $language ) {

			foreach ( [ 'general', 'titles' ] as $option ) {
				$data = get_option( "rank-math-options-{$option}_$lang_code" );
				if ( empty( $data ) ) {
					continue;
				}

				$common_data = array_intersect( array_keys( $data ), $this->get_translatable_options() );
				if ( empty( $common_data ) ) {
					continue;
				}

				foreach ( $common_data as $option_key ) {
					$string_id = icl_get_string_id( Helper::get_settings( "$option.$option_key" ), "admin_texts_rank-math-options-$option" ); // @phpstan-ignore-line
					icl_add_string_translation( $string_id, $lang_code, $data[ $option_key ], 10 ); // @phpstan-ignore-line
				}
			}
		}

		update_option( 'rank_math_wpml_data_converted', true );
	}

	/**
	 * Get Translatable option keys.
	 *
	 * @return array
	 */
	private function get_translatable_options() {
		$options = [
			'img_alt_format',
			'img_title_format',
			'breadcrumbs_separator',
			'breadcrumbs_prefix',
			'breadcrumbs_home_link',
			'breadcrumbs_home_label',
			'breadcrumbs_archive_format',
			'breadcrumbs_search_format',
			'breadcrumbs_404_label',
			'rss_before_content',
			'rss_after_content',

			'title_separator',
			'homepage_title',
			'homepage_description',
			'homepage_facebook_title',
			'homepage_facebook_description',
			'author_archive_title',
			'author_archive_description',
			'date_archive_title',
			'date_archive_description',
			'search_title',
			'404_title',
		];

		$post_types = Helper::get_accessible_post_types();
		foreach ( $post_types as $post_type => $data ) {
			$options = array_merge(
				$options,
				[
					"pt_{$post_type}_title",
					"pt_{$post_type}_description",
					"pt_{$post_type}_archive_title",
					"pt_{$post_type}_archive_description",
					"pt_{$post_type}_default_snippet_name",
					"pt_{$post_type}_default_snippet_desc",
				]
			);
		}

		$taxonomies = Helper::get_accessible_taxonomies();
		foreach ( $taxonomies as $taxonomy => $data ) {
			$options = array_merge(
				$options,
				[
					"tax_{$taxonomy}_title",
					"tax_{$taxonomy}_description",
				]
			);
		}

		return $options;
	}

	/**
	 * Maybe add notice on Permalinks page about the risks of changing the permalinks on a live site.
	 *
	 * @return void
	 */
	public function permalink_changes_warning() {
		global $pagenow;
		if ( 'options-permalink.php' !== $pagenow ) {
			return;
		}

		$this->action( 'admin_enqueue_scripts', 'add_permalink_changes_warning', 12 );
	}

	/**
	 * Add the notice for the Permalinks page.
	 *
	 * @return void
	 */
	public function add_permalink_changes_warning() {
		wp_enqueue_script( 'rank-math-core-permalink-settings' );
		$message = __( '<b>Rank Math Warning:</b> Changing the permalinks on a live, indexed site may result in serious loss of traffic if done incorrectly. Consider adding a new redirection from the old URL format to the new one.', 'rank-math' );
		Helper::add_notification(
			$message,
			[
				'type'    => 'warning',
				'screen'  => 'options-permalink',
				'classes' => 'hidden rank-math-notice-permalinks-warning is-dismissible',
			]
		);
	}
}
