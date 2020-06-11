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
}
