<?php
/**
 * Help getting started tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Help
 */

use RankMath\KB;
?>
<h3><?php esc_html_e( 'Getting started', 'rank-math' ); ?></h3>
<p>
	<?php esc_html_e( 'Congratulations on installing Rank Math. We hope that you\'ve had no issues with installing Rank Math on your website. After the installation, the next step is to run the setup wizard and configure Rank Math.', 'rank-math' ); ?>
</p>
<p>
	<?php esc_html_e( 'While Rank Math automatically initiates the setup wizard once you install it, it is possible to skip it accidentally. If you’ve also skipped Rank Math’s setup wizard, then you can rerun the setup wizard by going to Rank Math’s dashboard, and then clicking the Setup Wizard tab.', 'rank-math' ); ?>
</p>
<p>
	<img src="<?php echo rank_math()->plugin_url() . 'assets/admin/img/help/rank-math-setup-wizard-tab.jpg'; ?>" alt="<?php esc_attr_e( 'Rank Math Setup Wizard Tab', 'rank-math' ); ?>" style="max-width: 100%; height: auto;" width="900" height="535" class="aligncenter size-full wp-image-327302" />
</p>
<p>
	<a href="<?php KB::the( 'how-to-setup' ); ?>" target="_blank"><?php esc_html_e( 'Click here to read full setup tutorial', 'rank-math' ); ?></a>
</p>
<p>
	<?php
	printf(
		/* translators: link to support */
		__( 'If you have any questions about the setup process or are facing difficulties in setting up Rank Math for your website, then get in touch with our support staff by <a href="%1$s" target="_blank">opening a support ticket</a>. The support staff is available 24x7x365 and will help you out with any issues that you have.', 'rank-math' ),
		'https://support.rankmath.com/'
	);
	?>
</p>
