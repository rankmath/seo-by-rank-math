<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.273
 *
 * @since      1.0.273
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

use RankMath\Helper;

/**
 * Enable AI Visibility module by default for existing installs.
 */
function rank_math_1_0_273_enable_ai_visibility_module() {
	Helper::update_modules( [ 'ai-visibility' => 'on' ] );
}

rank_math_1_0_273_enable_ai_visibility_module();
