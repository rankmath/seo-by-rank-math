<?php
/**
 * On-Screen help tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

defined( 'ABSPATH' ) || exit;

?>
<p>
	<?php esc_html_e( 'Here you can set up custom redirections. It is important to choose the right type of redirection.', 'rank-math' ); ?>
</p>
<ul>
	<li><?php echo wp_kses_post( __( '301 redirections are <em>permananent</em>. The old URL will be removed in search engines and replaced by the new one, passing on SearchRank and other SEO scores. Browsers may also store the new URL in cache and redirect to it even after the redirection is deleted from the list here.', 'rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( 'Using a 302 <em>temporary</em> redirection is useful when you want to test a new page for client feedback temporarily without affecting the SEO scores of the original page.', 'rank-math' ) ); ?></li>
	<li><?php echo wp_kses_post( __( 'Redirections can be exported to your .htaccess file for faster redirections, in SEO > Settings > Import/Export.', 'rank-math' ) ); ?></li>
</ul>
