<?php
/**
 * The Schema_Markup wizard step
 *
 * @since      1.0.32
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
class Schema_Markup implements Wizard_Step {

	/**
	 * Get Localized data to be used in the Compatibility step.
	 *
	 * @return array
	 */
	public static function get_localized_data() {
		return array_merge(
			self::get_default_values(),
			[
				'rich_snippet'        => Helper::is_module_active( 'rich-snippet' ),
				'accessiblePostTypes' => Helper::get_accessible_post_types(),
				'knowledgegraph_type' => Helper::get_settings( 'titles.knowledgegraph_type' ),
				'schemaTypes'         => Helper::choices_rich_snippet_types( esc_html__( 'None (Click here to set one)', 'rank-math' ) ),
				'reviewPosts'         => Helper::get_review_posts(),
			]
		);
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
		Helper::update_modules( [ 'rich-snippet' => $values['rich_snippet'] ? 'on' : 'off' ] );

		// Schema.
		if ( $values['rich_snippet'] ) {
			self::save_rich_snippet( $settings, $values );
		}
		Helper::update_all_settings( $settings['general'], $settings['titles'], null );

		return Helper::get_admin_url();
	}

	/**
	 * Save rich snippet values for post type.
	 *
	 * @param array $settings Array of setting.
	 * @param array $values   Values to save.
	 */
	private static function save_rich_snippet( &$settings, $values ) {
		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$id           = 'pt_' . $post_type . '_default_rich_snippet';
			$article_type = 'pt_' . $post_type . '_default_article_type';

			$settings['titles'][ $id ]           = sanitize_text_field( $values[ $id ] );
			$settings['titles'][ $article_type ] = sanitize_text_field( $values[ $article_type ] );
		}
	}

	/**
	 * Get Default values for the schemas used for Post types.
	 */
	private static function get_default_values() {
		$richsnp_default = [
			'post'    => 'article',
			'product' => 'product',
		];

		$data = [];

		foreach ( Helper::get_accessible_post_types() as $post_type ) {
			if ( 'attachment' === $post_type ) {
				continue;
			}

			$field_id = 'pt_' . $post_type . '_default_rich_snippet';
			$default  = $post_type === 'product' ? 'product' : 'off';
			$value    = Helper::get_settings( 'titles.pt_' . $post_type . '_default_rich_snippet', ( isset( $richsnp_default[ $post_type ] ) ? $richsnp_default[ $post_type ] : $default ) );

			$data[ $field_id ] = $value ? $value : 'off';

			if ( $post_type === 'product' ) {
				continue;
			}

			$data[ 'pt_' . $post_type . '_default_article_type' ] = Helper::get_settings( 'titles.pt_' . $post_type . '_default_article_type', 'post' === $post_type ? 'BlogPosting' : 'Article' );
		}

		return $data;
	}
}
