<?php
/**
 * 404 Monitor inline help.
 *
 * @package    RankMath
 * @subpackage RankMath\Monitor
 */

?>
<p>
	<?php esc_html_e( 'Hovering over a row in the list will display action links that allow you to manage the item. You can perform the following actions:', 'rank-math' ); ?>
</p>
<ul>
	<li><?php echo wp_kses_post( __( '<strong>View Details</strong> shows details about the 404 requests.', 'rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Redirect</strong> takes you to the Redirections manager to redirect the 404 URL.', 'rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Delete</strong> permanently removes the item from the list.', 'rank-math' ) ); ?></li>
</ul>
