<?php
/**
 * Help AMP tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Help
 */

use RankMath\Helper;
use RankMath\KB;
?>
<h3><?php esc_html_e( 'AMP', 'rank-math' ); ?></h3>
<p>
	<?php esc_html_e( 'Accelerated Mobile Pages, or AMP for short, is a way for you to create fast loading pages that load blazing fast on mobile browsers. AMPs deliver consistent performance across multiple mobile devices.', 'rank-math' ); ?>
</p>
<p>
	<?php esc_html_e( 'Because they work so well and help pages load fast, Google has come to love the AMP enabled websites over their counterparts when it comes to rankings in the SERPs.', 'rank-math' ); ?>
</p>
<p>
	<?php esc_html_e( 'That is why having an AMP enabled website is so essential these days. But, having an AMP website brings its challenges with regards to the SEO. When you create AMPs, there are two versions of your site - one for the regular desktop browsers and one for the mobile browsers. If not handled properly, AMPs can backfire and create duplicate content issues which lead to de-ranked or worse, de-indexing of your posts.', 'rank-math' ); ?>
</p>
<p>
	<?php esc_html_e( 'Thankfully, we have thought this through and equipped Rank Math with the right tools to make sure your AMPS help your SEO, not ruin it.', 'rank-math' ); ?>
</p>
<p>
	<?php
	printf(
		/* translators: link to module dashboard */
		__( 'To get the most out of AMP along with Rank Math - simply install any of the below AMP plugin and then <a href="%1$s" target="_blank">enable the AMP module of Rank Math</a>.', 'rank-math' ),
		Helper::get_admin_url()
	);
	?>
</p>
<ol>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'amp-plugin' ); ?>"><?php esc_html_e( 'AMP for WordPress', 'rank-math' ); ?></a> </li>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'amp-wp' ); ?>"><?php esc_html_e( 'AMP for WP', 'rank-math' ); ?></a></li>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'amp-ninja' ); ?>"><?php esc_html_e( 'WP AMP Ninja', 'rank-math' ); ?></a></li>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'amp-weeblramp' ); ?>"><?php esc_html_e( 'AMP on WordPress – weeblrAMP CE', 'rank-math' ); ?></a></li>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'amp-woocommerce' ); ?>"><?php esc_html_e( 'AMP for WooCommerce', 'rank-math' ); ?></a></li>
	<li><a target="_blank" rel="nofollow noopener noreferrer" href="<?php KB::the( 'wp-amp' ); ?>"><?php esc_html_e( 'WP AMP', 'rank-math' ); ?></a></li>
</ol>
<p>
	<?php _e( 'If you face any issues with it, simply <a rel="nofollow noopener noreferrer" href="https://support.rankmath.com/" target="_blank">open a support ticket here</a>.', 'rank-math' ); ?>
</p>
