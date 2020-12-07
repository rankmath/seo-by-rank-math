<?php
/**
 * Main template for 404 monitor
 *
 * @package    RankMath
 * @subpackage RankMath\Monitor
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$monitor = Helper::get_module( '404-monitor' )->admin;
$monitor->table->prepare_items();
?>
<div class="wrap rank-math-404-monitor-wrap">

	<h2>
		<?php echo esc_html( get_admin_page_title() ); ?>
		<?php $monitor->page_title_actions(); ?>
	</h2>

	<?php \do_action( 'rank_math/404_monitor/before_list_table', $monitor ); ?>

	<form method="get">
		<input type="hidden" name="page" value="rank-math-404-monitor">
		<?php $monitor->table->search_box( esc_html__( 'Search', 'rank-math' ), 's' ); ?>
	</form>
	<form method="post">
		<?php $monitor->table->display(); ?>
	</form>

</div>
