<?php //phpcs:ignore WordPress.Files.FileName.NotHyphenatedLowercase -- This filename format is intentionally used to match the plugin version.
/**
 * The Updates routine for version 1.0.98
 *
 * @since      1.0.98
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Get a group's ID based on its name/slug.
 *
 * @param string $slug The string name of a group.
 *
 * @return int The group's ID, if it exists or is created, or 0 if it does not exist and is not created.
 */
function rank_math_1_0_98_as_get_group_id( $slug ) {
	if ( empty( $slug ) ) {
		return 0;
	}

	global $wpdb;
	$table    = $wpdb->prefix . 'actionscheduler_groups';
	$group_id = (int) $wpdb->get_var( $wpdb->prepare( "SELECT group_id FROM {$table} WHERE slug=%s", $slug ) );

	return $group_id;
}

/**
 * Fix Action Scheduler groups.
 */
function rank_math_1_0_98_fix_as_groups() {
	$workflow_group_id    = rank_math_1_0_98_as_get_group_id( 'workflow' );
	$inspections_group_id = rank_math_1_0_98_as_get_group_id( 'rank_math/analytics/get_inspections_data' );
	$rank_math_group_id   = rank_math_1_0_98_as_get_group_id( 'rank-math' );

	if ( 0 === $rank_math_group_id ) {
		return;
	}

	// In the actions table, update the group_id for all actions in the 'workflow' & 'rank_math/analytics/get_inspections_data' groups to the 'rank-math' group.
	global $wpdb;
	$actions_table = $wpdb->prefix . 'actionscheduler_actions';
	$wpdb->query( $wpdb->prepare( "UPDATE {$actions_table} SET group_id=%d WHERE group_id=%d OR group_id=%d", $rank_math_group_id, $workflow_group_id, $inspections_group_id ) );

	// Delete the 'workflow' & 'rank_math/analytics/get_inspections_data' groups.
	$groups_table = $wpdb->prefix . 'actionscheduler_groups';
	$wpdb->query( $wpdb->prepare( "DELETE FROM {$groups_table} WHERE group_id=%d OR group_id=%d", $workflow_group_id, $inspections_group_id ) );
}

rank_math_1_0_98_fix_as_groups();
