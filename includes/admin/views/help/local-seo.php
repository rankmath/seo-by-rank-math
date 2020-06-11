<?php
/**
 * Local SEO tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Help
 */

use RankMath\KB;
use RankMath\Helper;
?>
<h3><?php esc_html_e( 'Local SEO', 'rank-math' ); ?></h3>
<p>
	<?php esc_html_e( 'Local SEO is a way for you to rank better for searches made by your people in the area where you operate. It is the best way for you to get your products and services in front of the local customers.', 'rank-math' ); ?>
</p>
<p>
	<?php esc_html_e( 'There are various methods for optimizing your website for local SEO but the easiest method is built right inside the Rank Math plugin.', 'rank-math' ); ?>
</p>
<p>
	<?php
	printf(
		/* translators: link to local-seo */
		__( 'Simply <a rel="nofollow noopener noreferrer" href="%1$s" target="_blank">setup your business as Local Business</a> during the setup wizard.', 'rank-math' ),
		KB::get( 'local-seo' )
	);
	?>
</p>
<p>
	<?php esc_html_e( 'Make sure the Local SEO & Google Knowledge Graph module is enabled.', 'rank-math' ); ?>
</p>
<p>
	<?php
	printf(
		/* translators: link to local seo settings */
		__( 'Then, head over to <a href="%1$s">Rank Math > Titles and Meta > Local SEO</a> and add more information about your Local Business like your Company Name, Logo, Email ID, Phone number, Address, And Contact/About pages.', 'rank-math' ),
		Helper::get_admin_url( 'options-titles#setting-panel-local' )
	);
	?>
</p>
<p>
	<?php
	printf(
		/* translators: link to support */
		__( 'If you face any sort of issue, <a href="%1$s" target="_blank">open a new ticket here</a> so that we can help.', 'rank-math' ),
		KB::get( 'rm-support' )
	);
	?>
</p>
