<?php
/**
 * Search console ui.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;
use MyThemeShop\Helpers\Param;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

$page = Param::get( 'page', '', FILTER_SANITIZE_SPECIAL_CHARS, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK );
$page = in_array( $page, [ 'rank-math-options-general', 'rank-math-analytics' ], true ) ? 'rank-math-options-general' : 'rank-math-wizard&step=analytics';
$url  = Admin_Helper::get_activate_url( admin_url( 'admin.php?analytics=1&page=' . $page ) );

$site_url_valid = Admin_Helper::is_site_url_valid();
$button_class   = 'button button-primary button-connect' . ( $site_url_valid ? ' button-animated' : ' disabled' );
?>
<?php Admin_Helper::maybe_show_invalid_siteurl_notice(); ?>

<div class="wp-core-ui rank-math-ui connect-wrap" style="margin-top: 30px;">
	<a href="<?php echo esc_url( $url ); ?>" class="<?php echo esc_attr( $button_class ); ?>" name="rank_math_activate"><?php echo esc_attr__( 'Connect Your Rank Math Account', 'rank-math' ); ?></a>
</div>
<div id="rank-math-pro-cta" class="analytics">
	<div class="rank-math-cta-box width-100 no-shadow no-padding">
		<h3><?php echo esc_attr__( 'Benefits of Connecting Rank Math Account', 'rank-math' ); ?></h3>
		<ul>
			<li><?php echo esc_attr__( 'Verify site ownership on Google Search Console in a single click', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Track page and keyword rankings with the Advanced Analytics module', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Easily set up Google Analytics without using another 3rd party plugin', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Automatically submit sitemaps to the Google Search Console', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Free keyword suggestions when entering a focus keyword', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Use our revolutionary SEO Analyzer to scan your website for SEO errors', 'rank-math' ); ?></li>
			<li><a href="<?php echo esc_url( KB::get( 'free-account-benefits' ) ); ?>" target="_blank"><?php echo esc_html__( 'Learn more about the benefits of connecting your account here.', 'rank-math' ); ?></a></li>
		</ul>
	</div>
</div>
<div id="rank-math-pro-cta" class="rank-math-privacy-box">
	<div class="rank-math-cta-table">
		<div class="rank-math-cta-body less-padding">
			<i class="dashicons dashicons-lock"></i>
			<p><?php printf( esc_html__( 'We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. %s', 'rank-math' ), '<a href="' . esc_url( KB::get( 'usage-policy' ) ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more.', 'rank-math' ) . '</a>' ); ?></p>
		</div>
	</div>
</div>
