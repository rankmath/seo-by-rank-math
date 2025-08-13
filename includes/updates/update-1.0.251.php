<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.251
 *
 * @since      1.0.251
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Update code needed to support React migration.
 */
function rank_math_1_0_251_migrate_react_settings() {
	\RankMath\Status\Backup::create_backup();
	update_option( 'rank_math_react_settings_ui', 'on', false );
}
rank_math_1_0_251_migrate_react_settings();
