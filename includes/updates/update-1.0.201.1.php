<?php
/**
 * The Updates routine for version 1.0.201.1
 *
 * @since      1.0.201.1
 * @package    RankMath
 * @subpackage RankMath\Updates
 * @author     Rank Math <support@rankmath.com>
 */

defined( 'ABSPATH' ) || exit;

/**
 * Remove the schedule event used to update the prompts.
 */
function rank_math_1_0_201_1_remove_prompt_event() {
	wp_clear_scheduled_hook( 'rank_math/content-ai/update_prompts' );
}
rank_math_1_0_201_1_remove_prompt_event();
