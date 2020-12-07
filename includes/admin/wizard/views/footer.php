<?php
/**
 * Setup wizard footer template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

defined( 'ABSPATH' ) || exit;

echo '</body>' . "\n";

CMB2_JS::enqueue();
rank_math()->json->output();
if ( function_exists( 'wp_print_media_templates' ) ) {
	wp_print_media_templates();
}
wp_print_footer_scripts();
wp_print_scripts( 'rank-math-wizard' );
wp_print_scripts( 'cmb2-scripts' );

echo '</html>';
