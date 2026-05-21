<?php
/**
 * On-Screen help tab: Available Actions.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

defined( 'ABSPATH' ) || exit;

?>
<p>
	<?php esc_html_e( 'Hovering over a row in the list will display action links that allow you to manage the item. You can perform the following actions:', 'seo-by-rank-math' ); ?>
</p>
<ul>
	<li><?php echo wp_kses_post( __( '<strong>Edit</strong> redirection details: from/to URLs and the redirection type.', 'seo-by-rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Activate/Deactivate</strong> redirections. Deactivated redirections do not take effect on your site.', 'seo-by-rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( '<strong>Delete</strong> permanently removes the redirection.', 'seo-by-rank-math' ) ); ?></li>
</ul>
