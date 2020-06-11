<?php
/**
 * Help 404 Monitor tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Monitor
 */

use RankMath\KB;
?>
<h3><?php esc_html_e( '404 Monitor', 'rank-math' ); ?></h3>
<p><?php esc_html_e( '404 errors happen when someone requests a page or file that doesn\'t exist - actually, it\'s more accurate to say the web server can\'t find the file.', 'rank-math' ); ?></p>

<h4><?php esc_html_e( '404s happen when:', 'rank-math' ); ?></h4>

<ol>
	<li><?php esc_html_e( 'Someone types the wrong address', 'rank-math' ); ?></li>
	<li><?php esc_html_e( 'Someone links to the wrong address', 'rank-math' ); ?></li>
	<li><?php esc_html_e( 'The file or page moves to a different address', 'rank-math' ); ?></li>
	<li><?php esc_html_e( 'The page or file is deleted', 'rank-math' ); ?></li>
	<li><?php esc_html_e( 'The page or file never existed', 'rank-math' ); ?></li>
</ol>

<p><?php esc_html_e( 'They\'re actually very common on the web. And they\'re never a good thing.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'If you visit a site, you probably want to read or view content on that site. You won\'t stay on the site if the content is missing. Serving 404 errors to your visitors is a good way to lose them.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'It doesn\'t matter if it\'s your fault, their fault, or someone else\'s. Maybe they clicked on a broken link caused by a typing mistake on another website. Maybe they cut-and-pasted a URL wrong. Or possibly they made a spelling mistake when they typed a URL manually.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'Maybe you changed the URL of an article to make it more appealing to the search engines.', 'rank-math' ); ?></p>

<p><?php esc_html_e( 'Whatever the cause, the result is a disappointed reader. So fixing 404 errors is a usability issue. Patching them up will result in more useful traffic to your site, and for many sites, that means more revenue.', 'rank-math' ); ?></p>

<p><a href="<?php KB::the( '404-monitor' ); ?>" target="_blank"><?php esc_html_e( 'Click here to read full 404 Monitor tutorial', 'rank-math' ); ?></a></p>
