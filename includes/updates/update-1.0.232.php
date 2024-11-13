<?php
/**
 * The Updates routine for version 1.0.232.
 *
 * @since      1.0.232
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Add indexes to the internal links table (target_post_id column).
 */
function rank_math_1_0_232_internal_links_table_indexes() {
    global $wpdb;

    $wpdb->query( "ALTER TABLE {$wpdb->prefix}rank_math_internal_links ADD INDEX target_post_id (target_post_id)" );
}
rank_math_1_0_232_internal_links_table_indexes();
