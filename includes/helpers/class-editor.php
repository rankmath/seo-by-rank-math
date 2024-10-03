<?php
/**
 * The Editor helpers.
 *
 * @since      1.0.9
 * @package    RankMath
 * @subpackage RankMath\Helpers
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Helpers;

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Editor class.
 */
class Editor {
	/**
	 * Can add editor.
	 *
	 * @return bool
	 */
	public static function can_add_editor() {
		return Helper::has_cap( 'onpage_general' ) ||
			Helper::has_cap( 'onpage_advanced' ) ||
			Helper::has_cap( 'onpage_snippet' ) ||
			Helper::has_cap( 'onpage_social' );
	}

	/**
	 * Add option to Lock Modified date in the editor.
	 *
	 * @return bool
	 */
	public static function can_add_lock_modified_date() {
		return apply_filters( 'rank_math/lock_modified_date', true );
	}
}
