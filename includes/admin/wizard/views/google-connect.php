<?php
/**
 * Search console ui.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;
use RankMath\Google\Authentication;

defined( 'ABSPATH' ) || exit;

// phpcs:disable
$is_authorized = Authentication::is_authorized();
$authorize   = ! $is_authorized ? ( '<div class="connect-wrap" style="margin-top: 30px;"><a href="' . esc_url( Authentication::get_auth_url() ) . '" class="button button-primary button-animated rank-math-authorize-account">' . esc_html__( 'Connect Google Services', 'rank-math' ) . '</a></div>' ) : '';
$deauthorize = $is_authorized ? '<button class="button button-primary rank-math-deauthorize-account">' . esc_html__( 'Disconnect Account', 'rank-math' ) . '</button>' : '';

echo $authorize . $deauthorize;
?>
<div id="rank-math-pro-cta" class="analytics">
	<div class="rank-math-cta-box width-100 no-shadow no-padding no-border">
		<h3><?php echo esc_attr__( 'Benefits of Connecting Google Account', 'rank-math' ); ?></h3>
		<ul>
			<li><?php echo esc_attr__( 'Verify site ownership on Google Search Console in a single click', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Track page and keyword rankings with the Advanced Analytics module', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Easily set up Google Analytics without using another 3rd party plugin', 'rank-math' ); ?></li>
			<li><?php echo esc_attr__( 'Automatically submit sitemaps to the Google Search Console', 'rank-math' ); ?></li>
			<li><a href="<?php echo KB::get( 'help-analytics', 'SW Analytics Step Benefits' ); ?>" target="_blank"><?php echo esc_html__( 'Learn more about the benefits of connecting your account here.', 'rank-math' ); ?></a></li>
		</ul>
	</div>
</div>
<div id="rank-math-pro-cta" class="rank-math-privacy-box">
	<div class="rank-math-cta-table">
		<div class="rank-math-cta-body less-padding">
			<i class="dashicons dashicons-lock"></i>
			<p><?php printf( esc_html__( 'We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. %s', 'rank-math' ), '<a href="' . KB::get( 'usage-policy', 'Analytics Privacy Notice' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more.', 'rank-math' ) . '</a>' ); ?></p>
		</div>
	</div>
</div>
