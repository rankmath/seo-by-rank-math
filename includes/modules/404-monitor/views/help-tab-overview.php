<?php
/**
 * 404 Monitor inline help.
 *
 * @package    RankMath
 * @subpackage RankMath\Monitor
 */

use RankMath\KB;

defined( 'ABSPATH' ) || exit;

?>

<p>
	<?php esc_html_e( 'With the 404 monitor you can see where users and search engines are unable to find your content.', 'rank-math' ); ?>
</p>

<p>
	<?php esc_html_e( 'Knowledge Base Articles:', 'rank-math' ); ?>
</p>

<ul>
	<li>
		<a href="<?php echo KB::get( '404-monitor' ); ?>" target="_blank">
			<?php esc_html_e( '404 Monitor', 'rank-math' ); ?>
</a>
	</li>
	<li>
		<a href="<?php echo KB::get( '404-monitor-settings' ); ?>" target="_blank">
			<?php esc_html_e( '404 Monitor Settings', 'rank-math' ); ?>
</a>
	</li>
	<li>
		<a href="<?php echo KB::get( 'fix-404' ); ?>" target="_blank">
			<?php esc_html_e( 'Fix 404 Errors', 'rank-math' ); ?>
</a>
	</li>
</ul>
