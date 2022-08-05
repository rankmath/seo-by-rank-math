<?php
/**
 * The Attachment helpers.
 *
 * @since      0.9.0
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Attachment trait.
 */
trait Attachment {

	/**
	 * Check if a post can be included in sitemap.
	 *
	 * @param  int $attachment_id Attachment ID to check.
	 * @return bool
	 */
	public static function attachment_in_sitemap( $attachment_id ) {
		if ( empty( $attachment_id ) ) {
			return false;
		}

		$exclude_sitemap = get_post_meta( $attachment_id, 'rank_math_exclude_sitemap', true );

		return empty( $exclude_sitemap );
	}

	/**
	 * Generate local path for an attachment image.
	 * Credit: https://wordpress.stackexchange.com/a/182519
	 *
	 * @param int    $attachment_id  Attachment ID.
	 * @param string $size Size.
	 */
	public static function get_scaled_image_path( $attachment_id, $size = 'thumbnail' ) {
		$file = get_attached_file( $attachment_id, true );
		if ( empty( $size ) || $size === 'full' ) {
			// For the original size get_attached_file is fine.
			return realpath( $file );
		}

		if ( ! wp_attachment_is_image( $attachment_id ) ) {
			return false; // the ID is not referring to a media.
		}

		$info = image_get_intermediate_size( $attachment_id, $size );
		if ( ! is_array( $info ) || ! isset( $info['file'] ) ) {
			return false; // Probably a bad size argument.
		}

		return realpath( str_replace( wp_basename( $file ), $info['file'], $file ) );
	}
}
