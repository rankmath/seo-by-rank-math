<?php
/**
 * The Sitemap wizard step
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Wizard
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Wizard;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Step class.
 */
class Sitemap implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		$post_types = self::get_post_types();
		$taxonomies = self::get_taxonomies();
		return [
			'sitemap'            => Helper::is_module_active( 'sitemap' ),
			'include_images'     => Helper::get_settings( 'sitemap.include_images' ),
			'postTypes'          => $post_types['post_types'],
			'sitemap_post_types' => $post_types['defaults'],
			'taxonomies'         => $taxonomies['taxonomies'],
			'sitemap_taxonomies' => $taxonomies['defaults'],
		];
	}

	/**
	 * Save handler for step.
	 *
	 * @param array $values Values to save.
	 *
	 * @return bool
	 */
	public static function save( $values ) {
		$settings = rank_math()->settings->all_raw();
		Helper::update_modules( [ 'sitemap' => $values['sitemap'] ? 'on' : 'off' ] );

		if ( $values['sitemap'] ) {
			$settings['sitemap']['include_images'] = $values['include_images'] ? 'on' : 'off';

			$settings = self::save_post_types( $settings, $values );
			$settings = self::save_taxonomies( $settings, $values );
			Helper::update_all_settings( null, null, $settings['sitemap'] );
		}

		Helper::schedule_flush_rewrite();
		return true;
	}

	/**
	 * Get post type data.
	 *
	 * @return array
	 */
	private static function get_post_types() {
		$p_defaults = [];
		$post_types = Helper::choices_post_types();
		if ( Helper::get_settings( 'general.attachment_redirect_urls', true ) ) {
			unset( $post_types['attachment'] );
		}

		foreach ( $post_types as $post_type => $object ) {
			if ( true === Helper::get_settings( "sitemap.pt_{$post_type}_sitemap" ) ) {
				$p_defaults[] = $post_type;
			}
		}

		return [
			'defaults'   => $p_defaults,
			'post_types' => $post_types,
		];
	}

	/**
	 * Get taxonomies data.
	 *
	 * @return array
	 */
	private static function get_taxonomies() {
		$t_defaults = [];
		$taxonomies = Helper::get_accessible_taxonomies();
		unset( $taxonomies['post_tag'], $taxonomies['post_format'], $taxonomies['product_tag'] );
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		foreach ( $taxonomies as $taxonomy => $label ) {
			if ( true === Helper::get_settings( "sitemap.tax_{$taxonomy}_sitemap" ) ) {
				$t_defaults[] = $taxonomy;
			}
		}

		return [
			'defaults'   => $t_defaults,
			'taxonomies' => $taxonomies,
		];
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
		$post_types = Helper::choices_post_types();
		if ( ! isset( $values['sitemap_post_types'] ) ) {
			$values['sitemap_post_types'] = [];
		}

		foreach ( $post_types as $post_type => $object ) {
			$settings['sitemap'][ "pt_{$post_type}_sitemap" ] = in_array( $post_type, $values['sitemap_post_types'], true ) ? 'on' : 'off';
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
		$taxonomies = Helper::get_accessible_taxonomies();
		$taxonomies = wp_list_pluck( $taxonomies, 'label', 'name' );
		if ( ! isset( $values['sitemap_taxonomies'] ) ) {
			$values['sitemap_taxonomies'] = [];
		}

		foreach ( $taxonomies as $taxonomy => $label ) {
			$settings['sitemap'][ "tax_{$taxonomy}_sitemap" ] = in_array( $taxonomy, $values['sitemap_taxonomies'], true ) ? 'on' : 'off';
		}

		return $settings;
	}
}
