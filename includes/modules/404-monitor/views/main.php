<?php
/**
 * Main template for 404 monitor
 *
 * @package    RankMath
 * @subpackage RankMath\Monitor
 */

use RankMath\Helper;
use RankMath\KB;

$monitor = Helper::get_module( '404-monitor' )->admin;
$monitor->table->prepare_items();
?>
<div class="wrap rank-math-404-monitor-wrap">

	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>

	<p style="width:540px;max-width:100%;margin-bottom:0;font-size:15px;">
		<?php
		printf(
			/* Translators: 1: link to Monitor docs 2: link to Fix 404 docs */
			__( 'Find out where users are unable to find your content with the 404 monitor tool. You can also learn more about how to %1$s and %2$s with Rank Math.', 'rank-math' ),
			'<a href="' . KB::get( '404-monitor' ) . '" target="_blank">' . _x( 'monitor', 'in 404 monitor description', 'rank-math' ) . '</a>',
			'<a href="' . KB::get( 'fix-404' ) . '" target="_blank">' . _x( 'fix 404s', 'in 404 monitor description', 'rank-math' ) . '</a>'
		);
		?>
	</p>
	<form method="get">
		<input type="hidden" name="page" value="rank-math-404-monitor">
		<?php $monitor->table->search_box( esc_html__( 'Search', 'rank-math' ), 's' ); ?>
	</form>
	<form method="post">
		<?php $monitor->table->display(); ?>
	</form>

</div>
