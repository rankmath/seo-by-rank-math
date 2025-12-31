<?php
/**
 * WPML Integration.
 *
 * @since      1.0.256
 * @package    RankMath
 * @subpackage RankMath\Core
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\ThirdParty;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * WPML class.
 */
class WPML {

	use Hooker;

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->action( 'save_post', 'save_post', 10, 3 );
		$this->filter( 'rank_math/schema/update', 'update_schema', 10, 2 );
	}

	/**
	 * Update schema action handler.
	 *
	 * @param int   $object_id Object ID.
	 * @param array $schemas Schemas.
	 *
	 * @return void
	 */
	public function update_schema( $object_id, $schemas ) {
		$settings      = get_option( 'icl_sitepress_settings', [] );
		$custom_fields = $settings['translation-management']['custom_fields_translation'] ?? [];

		foreach ( $schemas as $meta_id => $schema ) {
			$type     = is_array( $schema['@type'] ) ? $schema['@type'][0] : $schema['@type'];
			$meta_key = 'rank_math_schema_' . $type;

			if ( ! isset( $custom_fields[ $meta_key ] ) ) {
				$custom_fields[ $meta_key ] = '2';
			}

			$settings['translation-management']['custom_fields_translation'] = $custom_fields;
		}

		update_option( 'icl_sitepress_settings', $settings );
	}

	/**
	 * Save post action handler.
	 *
	 * @param int     $post_id Post ID.
	 * @param WP_Post $post    Post object.
	 * @param bool    $update  Whether this is an existing post being updated or not.
	 *
	 * @return void
	 */
	public function save_post( $post_id, $post, $update ) {
		if (
			'revision' === $post->post_type ||
			'publish' !== $post->post_status ||
			! isset( $_POST['skip_sitepress_actions'] ) ||
			! sanitize_text_field( wp_unslash( $_POST['skip_sitepress_actions'] ) ) ||
			( isset( $_POST['post_ID'] ) && (int) $_POST['post_ID'] !== $post_id )
		) {
			return;
		}

		global $rm_wpml_translated_post_id;

		if ( ! $update || ! $rm_wpml_translated_post_id ) {
			$rm_wpml_translated_post_id = $post_id;
			return;
		}

		$rm_wpml_translated_post_id = 0;
		$content                    = $post->post_content;
		$block_updated              = $this->maybe_update_schema_blocks( $content, $post_id );
		$shortcode_updated          = $this->maybe_update_schema_shortcodes( $content );

		if ( ! $block_updated && ! $shortcode_updated ) {
			return;
		}

		wp_update_post(
			[
				'ID'           => $post_id,
				'post_content' => $content,
			]
		);
	}

	/**
	 * Maybe update schema blocks.
	 *
	 * @param string $content Post content.
	 * @param int    $post_id Post ID.
	 */
	private function maybe_update_schema_blocks( &$content, $post_id ) {
		preg_match_all( '/<!--\s*wp:rank-math\/rich-snippet\s*(\{.*\})\s*\/-->/m', $content, $blocks, PREG_SET_ORDER );
		if ( empty( $blocks ) ) {
			return false;
		}

		foreach ( $blocks as $block ) {
			$attr            = json_decode( $block[1], true );
			$attr['post_id'] = $post_id;
			$content         = str_replace( $block[0], '<!-- wp:rank-math/rich-snippet ' . wp_json_encode( $attr, JSON_UNESCAPED_SLASHES ) . ' /-->', $content );
		}

		return true;
	}

	/**
	 * Maybe update schema shortcodes.
	 *
	 * @param string $content Post content.
	 */
	private function maybe_update_schema_shortcodes( &$content ) {
		$pattern = '/\[rank_math_rich_snippet\s+([^\]]*)\]/';
		if ( ! preg_match_all( $pattern, $content, $shortcodes, PREG_SET_ORDER ) ) {
			return false;
		}

		$updated = false;
		foreach ( $shortcodes as $shortcode ) {
			$attributes = $shortcode[1];

			// Remove id attribute with double quotes: id="...".
			$new_attributes = preg_replace( '/id\s*=\s*"[^"]*"/', 'id=""', $attributes );

			// Remove id attribute with single quotes: id='...'.
			$new_attributes = preg_replace( "/id\s*=\s*'[^']*'/", "id=''", $new_attributes );

			if ( $new_attributes !== $attributes ) {
				$content = str_replace( $shortcode[0], '[rank_math_rich_snippet ' . $new_attributes . ']', $content );
				$updated = true;
			}
		}

		return $updated;
	}
}
