<?php
/**
 * The Your Site wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Your_Site implements Wizard_Step {
	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$displayname = self::get_site_display_name();
		$data        = [
			'site_type'              => self::get_default_site_type(),
			'businessTypesChoices'   => Helper::choices_business_types(),
			'business_type'          => Helper::get_settings( 'titles.local_business_type' ),
			'website_name'           => Helper::get_settings( 'titles.website_name', $displayname ),
			'website_alternate_name' => Helper::get_settings( 'titles.website_alternate_name', '' ),
			'company_name'           => Helper::get_settings( 'titles.knowledgegraph_name', $displayname ),
			'open_graph_image'       => Helper::get_settings( 'titles.open_graph_image' ),
		];

		$company_logo = self::get_default_logo();
		if ( $company_logo ) {
			$data['company_logo']    = $company_logo;
			$data['company_logo_id'] = attachment_url_to_postid( $company_logo );
		}

		$open_graph_image = Helper::get_settings( 'titles.open_graph_image' );
		if ( $open_graph_image ) {
			$data['open_graph_image']    = $open_graph_image;
			$data['open_graph_image_id'] = attachment_url_to_postid( $open_graph_image );
		}

		return $data;
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		$settings = wp_parse_args(
			rank_math()->settings->all_raw(),
			[
				'titles'  => '',
				'sitemap' => '',
			]
		);
		$values   = wp_parse_args(
			$values,
			[
				'company_name'        => '',
				'company_logo'        => '',
				'company_logo_id'     => '',
				'open_graph_image'    => '',
				'open_graph_image_id' => '',
			]
		);

		// Save these settings.
		$functions = [ 'save_local_seo', 'save_open_graph', 'save_post_types', 'save_taxonomies' ];
		foreach ( $functions as $function ) {
			$settings = self::$function( $settings, $values );
		}

		$business_type = [ 'news', 'business', 'webshop', 'otherbusiness' ];
		$modules       = [ 'local-seo' => in_array( $values['site_type'], $business_type, true ) ? 'on' : 'off' ];
		$users         = get_users( [ 'role__in' => [ 'administrator', 'editor', 'author', 'contributor' ] ] );

		if ( count( $users ) > 1 && ! is_plugin_active( 'members/members.php' ) ) {
			$modules['role-manager'] = 'on';
		}

		set_transient( '_rank_math_site_type', sanitize_text_field( $values['site_type'] ) );
		Helper::update_modules( $modules );
		Helper::update_all_settings( null, $settings['titles'], null );

		return true;
	}

	/**
	 * Save Local Seo
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private static function save_local_seo( $settings, $values ) {
		switch ( $values['site_type'] ) {
			case 'blog':
			case 'portfolio':
				$settings['titles']['knowledgegraph_type']    = 'person';
				$settings['titles']['knowledgegraph_name']    = sanitize_text_field( $values['company_name'] );
				$settings['titles']['knowledgegraph_logo']    = sanitize_url( $values['company_logo'] );
				$settings['titles']['knowledgegraph_logo_id'] = absint( $values['company_logo_id'] );
				break;

			case 'news':
			case 'webshop':
			case 'business':
			case 'otherbusiness':
				$settings['titles']['knowledgegraph_type']    = 'company';
				$settings['titles']['knowledgegraph_name']    = sanitize_text_field( $values['company_name'] );
				$settings['titles']['knowledgegraph_logo']    = sanitize_url( $values['company_logo'] );
				$settings['titles']['local_business_type']    = sanitize_text_field( $values['business_type'] );
				$settings['titles']['knowledgegraph_logo_id'] = absint( $values['company_logo_id'] );
				break;

			case 'otherpersonal':
				$settings['titles']['knowledgegraph_type'] = 'person';
				$settings['titles']['knowledgegraph_name'] = sanitize_text_field( $values['company_name'] );
				break;
		}

		foreach ( [ 'website_name', 'website_alternate_name' ] as $key ) {
			if ( empty( $values[ $key ] ) ) {
				continue;
			}

			$settings['titles'][ $key ] = sanitize_text_field( $values[ $key ] );
		}

		return $settings;
	}

	/**
	 * Save Open Graph
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private static function save_open_graph( $settings, $values ) {
		if ( ! empty( $values['open_graph_image_id'] ) ) {
			$settings['titles']['open_graph_image']    = sanitize_url( $values['open_graph_image'] );
			$settings['titles']['open_graph_image_id'] = absint( $values['open_graph_image_id'] );
		}

		if ( empty( $values['company_logo_id'] ) ) {
			unset( $settings['titles']['knowledgegraph_logo'] );
			unset( $settings['titles']['knowledgegraph_logo_id'] );
		}

		return $settings;
	}

	/**
	 * Save Post Types
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private static function save_post_types( $settings, $values ) {
		foreach ( Helper::get_accessible_post_types() as $post_type => $label ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$settings['titles'][ "pt_{$post_type}_add_meta_box" ] = 'on';
		}

		return $settings;
	}

	/**
	 * Save Taxonomies
	 *
	 * @param array $settings Array of all settings.
	 * @param array $values   Array of posted values.
	 *
	 * @return array
	 */
	private static function save_taxonomies( $settings, $values ) {
		$taxonomies = Admin_Helper::get_taxonomies_options();
		array_shift( $taxonomies );
		foreach ( $taxonomies as $taxonomy => $label ) {
			$settings['titles'][ "tax_{$taxonomy}_add_meta_box" ] = 'on';
		}

		return $settings;
	}

	/**
	 * Get site display name.
	 *
	 * @return string
	 */
	protected static function get_site_display_name() {
		$siteurl  = get_bloginfo( 'url' );
		$sitename = get_bloginfo( 'title' );

		return $sitename ? $sitename : $siteurl;
	}

	/**
	 * Get default logo.
	 *
	 * @return string
	 */
	private static function get_default_logo() {
		if ( defined( 'MTS_THEME_NAME' ) && MTS_THEME_NAME ) {
			$theme_options = get_option( MTS_THEME_NAME );
			if ( isset( $theme_options['mts_logo'] ) ) {
				return wp_get_attachment_url( $theme_options['mts_logo'] );
			}
		}

		if ( current_theme_supports( 'custom-logo' ) && ! empty( get_theme_mod( 'custom_logo' ) ) ) {
			return wp_get_attachment_url( get_theme_mod( 'custom_logo' ) );
		}

		return Helper::get_settings( 'titles.knowledgegraph_logo' );
	}

	/**
	 * Get default site type.
	 *
	 * @return string
	 */
	private static function get_default_site_type() {
		$default_type = get_transient( '_rank_math_site_type' );
		return $default_type ? $default_type : ( class_exists( 'Easy_Digital_Downloads' ) || class_exists( 'WooCommerce' ) ? 'webshop' : 'blog' );
	}

	/**
	 * Get type dependency.
	 *
	 * @return array
	 */
	private function get_type_dependency() {
		return [
			[ 'site_type', 'news' ],
			[ 'site_type', 'business' ],
			[ 'site_type', 'webshop' ],
			[ 'site_type', 'otherbusiness' ],
		];
	}

	/**
	 * Get type choices.
	 *
	 * @return array
	 */
	private function get_type_choices() {
		return [
			'blog'          => esc_html__( 'Personal Blog', 'rank-math' ),
			'news'          => esc_html__( 'Community Blog/News Site', 'rank-math' ),
			'portfolio'     => esc_html__( 'Personal Portfolio', 'rank-math' ),
			'business'      => esc_html__( 'Small Business Site', 'rank-math' ),
			'webshop'       => esc_html__( 'Webshop', 'rank-math' ),
			'otherpersonal' => esc_html__( 'Other Personal Website', 'rank-math' ),
			'otherbusiness' => esc_html__( 'Other Business Website', 'rank-math' ),
		];
	}
}
