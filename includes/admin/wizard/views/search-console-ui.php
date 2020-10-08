<?php
/**
 * Search console ui.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;
use RankMath\Helper;
use MyThemeShop\Helpers\Param;
use RankMath\Admin\Admin_Helper;
use RankMath\Google\Authentication;

// phpcs:disable
if ( ! Helper::is_site_connected() ) {
	require_once 'rank-math-connect.php';
	return;
}

$is_authorized = Authentication::is_authorized();
if ( ! $is_authorized ) {
	require_once 'google-connect.php';
	return;
}

$profile = wp_parse_args(
	get_option( 'rank_math_google_analytic_profile' ),
	[
		'profile' => '',
		'country' => 'all',
	]
);
$analytics = wp_parse_args(
	get_option( 'rank_math_google_analytic_options' ),
	[
		'adsense_id'       => '',
		'account_id'       => '',
		'property_id'      => '',
		'view_id'          => '',
		'country'          => 'all',
		'install_code'     => false,
		'anonymize_ip'     => false,
		'exclude_loggedin' => false,
	]
);
$is_profile_connected   = ! empty( $profile['profile'] );
$is_adsense_connected   = ! empty( $analytics ) && ! empty( $analytics['adsense_id'] );
$is_analytics_connected = ! empty( $analytics ) && ! empty( $analytics['view_id'] );
?>
<input type="hidden" class="cmb2-id-check-all-services" value="0" />

<div class="disconnect-wrap">
	<button class="button button-link rank-math-disconnect-google"><?php esc_html_e( 'Disconnect', 'rank-math' ); ?></button>
</div>

<div class="rank-math-box no-padding rank-math-accordion <?php echo $is_profile_connected ? 'connected' : 'disconnected'; ?>" tabindex="0">
	<header>
		<h3><?php esc_html_e( 'Search Console', 'rank-math' ); ?></h3>
	</header>
	<div class="rank-math-accordion-content">

		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-console-profile"><?php esc_html_e( 'Site', 'rank-math' ); ?></label>
				<select class="cmb2_select site-console-profile notrack" name="site-console-profile" id="site-console-profile" data-selected="<?php echo $profile['profile']; ?>" disabled="disabled"></select>
			</div>
			<?php do_action( 'rank_math/analytics/options/console' ); ?>
		</div>

		<footer>
			<button class="button button-primary rank-math-save-profiles"><?php esc_html_e( 'Save', 'rank-math' ); ?></button>
			<button class="button button-secondary rank-math-accordion-close"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></button>
		</footer>
	</div>
</div>

<div class="rank-math-box no-padding rank-math-accordion is-open <?php echo $is_analytics_connected ? 'connected' : 'disconnected'; ?>" tabindex="0">
	<header>
		<h3><?php esc_html_e( 'Analytics', 'rank-math' ); ?></h3>
	</header>
	<div class="rank-math-accordion-content">
		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-analytics-account"><?php esc_html_e( 'Account', 'rank-math' ); ?></label>
				<select class="cmb2_select site-analytics-account notrack" name="site-analytics-account" id="site-analytics-account" data-selected="<?php echo esc_attr( $analytics['account_id'] ); ?>" disabled="disabled">
					<option value="0">Select Account</option>
				</select>
			</div>
			<div class="cmb-row-col">
				<label for="site-analytics-property"><?php esc_html_e( 'Property', 'rank-math' ); ?></label>
				<select class="cmb2_select site-analytics-property notrack" name="site-analytics-property" id="site-analytics-property" data-selected="<?php echo esc_attr( $analytics['property_id'] ); ?>" disabled="disabled">
					<option value="0">Select Property</option>
				</select>
			</div>
			<div class="cmb-row-col">
				<label for="site-analytics-view"><?php esc_html_e( 'View', 'rank-math' ); ?></label>
				<select class="cmb2_select site-analytics-view notrack" name="site-analytics-view" id="site-analytics-view" data-selected="<?php echo esc_attr( $analytics['view_id'] ); ?>" disabled="disabled">
					<option value="0">Select Web View</option>
				</select>
			</div>
			<?php do_action( 'rank_math/analytics/options/analytics' ); ?>
			<div class="cmb-row-col create-new-view" style="display: none">
				<label for="site-analytics-view"><?php esc_html_e( 'Create a new View', 'rank-math' ); ?></label>
				<input type="text" class="new-view regular-text notrack" value="All Web Site Data">
				<button class="button button-primary create-view"><?php esc_html_e( 'Create', 'rank-math' ); ?></button>
				<button class="button button-secondary close-create-new"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></button>
			</div>
		</div>

		<div class="cmb-row cmb-type-toggle">
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="install-code" id="install-code" value="on"<?php checked( $analytics['install_code'] ); ?>>
					<span class="cmb2-slider">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="install-code"><?php esc_html_e( 'Install analytics code', 'rank-math' ); ?></label>
			</div>
		</div>

		<div class="cmb-row cmb-type-toggle">
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="anonymize-ip" id="anonymize-ip" value="on"<?php checked( $analytics['anonymize_ip'] ); ?> disabled>
					<span class="cmb2-slider disabled">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="anonymize-ip">
					<?php esc_html_e( 'Anonymize IP addresses', 'rank-math' ); ?>
					<span class="rank-math-pro-badge">
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Anonymize%20IP&utm_campaign=WP" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'PRO', 'rank-math' ); ?>
						</a>
					</span>
				</label>
				<div class="rank-math-cmb-dependency hidden" data-relation="or">
					<span class="hidden" data-field="install-code" data-comparison="=" data-value="on"></span>
				</div>
			</div>
		</div>

		<div class="cmb-row cmb-type-toggle">
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="exclude-loggedin" id="exclude-loggedin" value="on"<?php checked( $analytics['exclude_loggedin'] ); ?>>
					<span class="cmb2-slider">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="exclude-loggedin"><?php esc_html_e( 'Exclude Logged-in users', 'rank-math' ); ?></label>
				<div class="rank-math-cmb-dependency hidden" data-relation="or">
					<span class="hidden" data-field="install-code" data-comparison="=" data-value="on"></span>
				</div>
			</div>
		</div>

		<footer>
			<button class="button button-primary rank-math-save-analytics"><?php esc_html_e( 'Save', 'rank-math' ); ?></button>
			<button class="button button-secondary rank-math-accordion-close"><?php esc_html_e( 'Cancel', 'rank-math' ); ?></button>
		</footer>
	</div>
</div>

<div class="rank-math-box no-padding rank-math-accordion <?php echo $is_adsense_connected ? 'connected' : 'disconnected'; ?>" tabindex="0">
	<header>
		<h3><?php esc_html_e( 'AdSense', 'rank-math-pro' ); ?></h3>
	</header>
	<div class="rank-math-accordion-content">
		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-adsense-account"><?php esc_html_e( 'Account', 'rank-math-pro' ); ?></label>
				<select class="cmb2_select site-adsense-account notrack" name="site-adsense-account" id="site-adsense-account" data-selected="<?php echo esc_attr( $analytics['adsense_id'] ); ?>" disabled="disabled">
					<option value="0">Select Account</option>
				</select>
			</div>
		</div>

		<div id="rank-math-pro-cta" class="no-margin">
			<div class="rank-math-cta-text">
				<span class="rank-math-pro-badge"><a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=AdSense%20Toggle&utm_campaign=WP" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PRO', 'rank-math' ); ?></a></span> Google AdSense support is only available in Rank Math Pro's Advanced Analytics module.
			</div>
		</div>

		<footer>
			<button class="button button-primary rank-math-save-adsense"><?php esc_html_e( 'Save', 'rank-math-pro' ); ?></button>
			<button class="button button-secondary rank-math-accordion-close"><?php esc_html_e( 'Cancel', 'rank-math-pro' ); ?></button>
		</footer>

	</div>
</div>

<div id="rank-math-pro-cta" class="rank-math-privacy-box width-100">
	<div class="rank-math-cta-table">
		<div class="rank-math-cta-body less-padding">
			<i class="dashicons dashicons-lock"></i>
			<p><?php printf( esc_html__( 'We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. %s', 'rank-math' ), '<a href="' . KB::get( 'usage-policy' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more.', 'rank-math' ) . '</a>' ); ?></p>
		</div>
	</div>
</div>
<?php
// phpcs:enable
