<?php
/**
 * Search console UI.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;
use RankMath\Helper;
use RankMath\Google\Authentication;
use RankMath\Google\Permissions;
use RankMath\Analytics\Url_Inspection;
use RankMath\Helpers\Str;
use RankMath\Google\Analytics;
use RankMath\Google\Console;

defined( 'ABSPATH' ) || exit;

if ( ! Helper::is_site_connected() ) {
	require_once 'rank-math-connect.php';
	return;
}

$is_authorized = Authentication::is_authorized();
if ( ! $is_authorized ) {
	require_once 'google-connect.php';
	return;
}

$profile   = wp_parse_args(
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
		'measurement_id'   => '',
		'stream_name'      => '',
		'country'          => 'all',
		'install_code'     => false,
		'anonymize_ip'     => false,
		'local_ga_js'      => false,
		'exclude_loggedin' => false,
	]
);

$is_profile_connected    = Console::is_console_connected();
$is_adsense_connected    = ! empty( $analytics['adsense_id'] );
$is_analytics_connected  = Analytics::is_analytics_connected();
$is_index_status_enabled = Url_Inspection::is_enabled() || ! $is_profile_connected;
$all_services            = get_option(
	'rank_math_analytics_all_services',
	[
		'isVerified'           => '',
		'inSearchConsole'      => '',
		'hasSitemap'           => '',
		'hasAnalytics'         => '',
		'hasAnalyticsProperty' => '',
		'homeUrl'              => '',
		'sites'                => '',
		'accounts'             => [],
		'adsenseAccounts'      => [],
	]
);
$is_pro_active           = defined( 'RANK_MATH_PRO_FILE' );
?>
<input type="hidden" class="cmb2-id-check-all-services" value="<?php echo $is_profile_connected && $is_analytics_connected ? '1' : '0'; ?>" />

<?php
$connections = [
	'reconnect'  => [
		'link'  => wp_nonce_url( admin_url( 'admin.php?reconnect=google' ), 'rank_math_reconnect_google' ),
		'class' => 'rank-math-reconnect-google',
		'text'  => esc_html__( 'Reconnect', 'rank-math' ),
	],
	'disconnect' => [
		'link'  => '#',
		'class' => 'rank-math-disconnect-google',
		'text'  => esc_html__( 'Disconnect', 'rank-math' ),
	],
];

if ( Helper::is_advanced_mode() && ( $is_profile_connected || $is_adsense_connected || $is_analytics_connected ) ) {
	$connections['test-connections'] = [
		'link'  => '#',
		'class' => 'rank-math-test-connection-google',
		'text'  => esc_html__( 'Test Connections', 'rank-math' ),
	];
}

$connections = apply_filters( 'rank_math/analytics/connect_actions', $connections );
?>
<div class="connect-actions">
	<?php foreach ( $connections as $connection ) { ?>
		<a href="<?php echo esc_attr( $connection['link'] ); ?>" class="button button-link <?php echo esc_attr( $connection['class'] ); ?>"><?php echo esc_html( $connection['text'] ); ?></a>
	<?php } ?>
</div>

<?php
$console_classes        = Helper::classnames(
	'rank-math-box no-padding rank-math-accordion rank-math-connect-search-console',
	[
		'connected'    => $is_profile_connected,
		'disconnected' => ! $is_profile_connected,
		'disabled'     => ! Permissions::has_console(),
	]
);
$console_status_classes = Helper::classnames(
	'rank-math-connection-status',
	[
		'rank-math-connection-status-success' => $is_profile_connected,
		'rank-math-connection-status-error'   => ! $is_profile_connected,
	]
);

$console_status = $is_profile_connected ? 'Connected' : 'Not Connected';

?>
<div class="<?php echo esc_attr( $console_classes ); ?>" tabindex="0">
	<header>
		<h3><span class="rank-math-connection-status-wrap"><span class="<?php echo esc_attr( $console_status_classes ); ?>" title="<?php echo esc_attr( $console_status ); ?>"></span></span> <?php esc_html_e( 'Search Console', 'rank-math' ); ?></h3>
	</header>
	<div class="rank-math-accordion-content">

		<?php
		if ( ! Permissions::has_console() ) {
			Permissions::print_warning();
		}
		?>

		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-console-profile"><?php esc_html_e( 'Site', 'rank-math' ); ?></label>
				<select class="cmb2_select site-console-profile notrack" name="site-console-profile" id="site-console-profile" data-selected="<?php echo esc_attr( $profile['profile'] ); ?>" disabled="disabled">
					<?php if ( $is_profile_connected ) : ?>
					<option value="<?php echo esc_attr( $profile['profile'] ); ?>"><?php echo esc_attr( $profile['profile'] ); ?></option>
					<?php endif; ?>
				</select>
			</div>
			<?php do_action( 'rank_math/analytics/options/console' ); ?>
		</div>

		<div class="cmb-row cmb-type-toggle">
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="enable-index-status" id="enable-index-status" value="on" <?php checked( $is_index_status_enabled ); ?> <?php disabled( ! $is_profile_connected ); ?>>
					<span class="cmb2-slider">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="enable-index-status"><?php esc_html_e( 'Enable the Index Status tab', 'rank-math' ); ?></label>
				<div class="cmb2-metabox-description"><?php esc_html_e( 'Enable this option to show the Index Status tab in the Analytics module.', 'rank-math' ); ?> <a href="<?php echo KB::get( 'url-inspection-api', 'SW Analytics Index Status Option' ); // phpcs:ignore ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'Learn more.', 'rank-math' ); ?></a></div>
			</div>
		</div>
	</div>
</div>

<?php
$analytic_classes        = Helper::classnames(
	'rank-math-box no-padding rank-math-accordion rank-math-connect-analytics',
	[
		'connected'    => $is_analytics_connected,
		'disconnected' => ! $is_analytics_connected,
		'disabled'     => ! Permissions::has_analytics(),
	]
);
$analytic_status_classes = Helper::classnames(
	'rank-math-connection-status',
	[
		'rank-math-connection-status-success' => $is_analytics_connected,
		'rank-math-connection-status-error'   => ! $is_analytics_connected,
	]
);
$analytic_status         = $is_analytics_connected ? 'Connected' : 'Not Connected';
?>
<div class="<?php echo esc_attr( $analytic_classes ); ?>" tabindex="0">
	<header>
		<h3><span class="rank-math-connection-status-wrap"><span class="<?php echo esc_attr( $analytic_status_classes ); ?>" title="<?php echo esc_attr( $analytic_status ); ?>"></span></span><?php esc_html_e( 'Analytics', 'rank-math' ); ?></h3>
	</header>
	<div class="rank-math-accordion-content rank-math-analytics-content">

		<?php
		if ( ! Permissions::has_analytics() ) {
			Permissions::print_warning();
		}
		?>

		<p class="warning yellow">
			<strong class="note"><?php echo esc_html__( 'Note', 'rank-math' ); ?></strong>
			<?php
			printf(
				/* translators: %s: Link to KB article */
				esc_html__( 'Ready to switch to Google Analytics 4? %s', 'rank-math' ),
				'<a href="' . KB::get( 'using-ga4', 'Analytics GA4 KB' ) . '" target="_blank">' . esc_html__( 'Click here to know how', 'rank-math' ) . '</a>.' // phpcs:ignore
			);
			?>
		</p>

		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-analytics-account"><?php esc_html_e( 'Account', 'rank-math' ); ?></label>
				<select class="cmb2_select site-analytics-account notrack" name="site-analytics-account" id="site-analytics-account" data-selected="<?php echo esc_attr( $analytics['account_id'] ); ?>" disabled="disabled">
					<?php
					if ( $is_analytics_connected ) :
						$analytic_account = $all_services['accounts'][ $analytics['account_id'] ];
						?>
					<option value="<?php echo esc_attr( $analytics['account_id'] ); ?>"><?php echo esc_attr( $analytic_account['name'] ); ?></option>
					<?php endif; ?>
				</select>
			</div>
			<div class="cmb-row-col">
				<label for="site-analytics-property"><?php esc_html_e( 'Property', 'rank-math' ); ?></label>
				<select class="cmb2_select site-analytics-property notrack" name="site-analytics-property" id="site-analytics-property" data-selected="<?php echo esc_attr( $analytics['property_id'] ); ?>" disabled="disabled">
					<?php
					if ( $is_analytics_connected ) :
						$analytic_property = $all_services['accounts'][ $analytics['account_id'] ]['properties'][ $analytics['property_id'] ]['name'];
						?>
					<option value="<?php echo esc_attr( $analytics['property_id'] ); ?>"><?php echo esc_html( $analytic_property ); ?></option>
					<?php endif; ?>
				</select>
			</div>
			<div class="cmb-row-col">
				<label for="site-analytics-view">
				<?php echo esc_html__( 'Data Stream', 'rank-math' ); ?>
				</label>
				<select class="cmb2_select site-analytics-view notrack" name="site-analytics-view" id="site-analytics-view" data-selected="<?php echo esc_attr( $analytics['view_id'] ); ?>" disabled="disabled">
					<?php
					if ( $is_analytics_connected ) :
						$analytic_view = $analytics['stream_name'] ? $analytics['stream_name'] : 'Website';
						?>
						<option value="<?php echo esc_attr( $analytics['view_id'] ); ?>"><?php echo esc_attr( $analytic_view ); ?></option>
						<?php
					endif;
					?>
				</select>
			</div>
			<input type="hidden" id="rank-math-analytics-measurement-id" name="measurementID" value="<?php echo esc_attr( $analytics['measurement_id'] ); ?>" />
			<input type="hidden" id="rank-math-analytics-stream-name" name="streamName" value="<?php echo esc_attr( $analytics['stream_name'] ); ?>" />
			<?php do_action( 'rank_math/analytics/options/analytics' ); ?>
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
				<div class="cmb2-metabox-description"><?php esc_html_e( 'Enable this option only if you are not using any other plugin/theme to install Google Analytics code.', 'rank-math' ); ?></div>
			</div>
		</div>

		<div class="cmb-row cmb-type-toggle <?php echo ! $is_pro_active ? 'cmb-redirector-element' : ''; ?>" <?php echo ! $is_pro_active ? 'data-url="' . KB::the( 'free-vs-pro', 'Anonymize IP' ) . '"' : ''; // phpcs:ignore ?>>
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="anonymize-ip" id="anonymize-ip" value="on"<?php checked( $analytics['anonymize_ip'] ); ?><?php disabled( ! $is_pro_active ); ?>>
					<span class="cmb2-slider<?php echo ! $is_pro_active ? ' disabled' : ''; ?> ">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="anonymize-ip">
					<?php esc_html_e( 'Anonymize IP addresses', 'rank-math' ); ?>
					<?php if ( ! $is_pro_active ) : ?>
					<span class="rank-math-pro-badge">
						<a href="<?php KB::the( 'pro', 'Anonymize IP' ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'PRO', 'rank-math' ); ?>
						</a>
					</span>
					<?php endif; ?>
				</label>
				<div class="rank-math-cmb-dependency hidden" data-relation="or">
					<span class="hidden" data-field="install-code" data-comparison="=" data-value="on"></span>
				</div>
			</div>
		</div>

		<div class="cmb-row cmb-type-toggle <?php echo ! $is_pro_active ? 'cmb-redirector-element' : ''; ?>" <?php echo ! $is_pro_active ? 'data-url="' . KB::the( 'pro', 'Localjs IP' ) . '"' : ''; // phpcs:ignore ?>>
			<div class="cmb-td">
				<label class="cmb2-toggle">
					<input type="checkbox" class="regular-text notrack" name="local-ga-js" id="local-ga-js" value="on"<?php checked( $analytics['local_ga_js'] ); ?><?php disabled( ! $is_pro_active ); ?>>
					<span class="cmb2-slider<?php echo ! $is_pro_active ? ' disabled' : ''; ?> ">
						<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
						<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
					</span>
				</label>
				<label for="local-ga-js">
					<?php esc_html_e( 'Self-Hosted Analytics JS File', 'rank-math' ); ?>
					<?php if ( ! $is_pro_active ) : ?>
					<span class="rank-math-pro-badge">
						<a href="<?php KB::the( 'pro', 'Localjs IP' ); ?>" target="_blank" rel="noopener noreferrer">
							<?php esc_html_e( 'PRO', 'rank-math' ); ?>
						</a>
					</span>
					<?php endif; ?>
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
	</div>
</div>

<?php ob_start(); ?>
<div class="rank-math-box no-padding rank-math-accordion rank-math-connect-adsense disconnected" tabindex="0">
	<header>
		<h3>
			<span class="rank-math-connection-status-wrap">
				<span class="rank-math-connection-status rank-math-connection-status-error" title="Not Connected"></span>
			</span><?php esc_html_e( 'AdSense', 'rank-math' ); ?>
		</h3>
	</header>
	<div class="rank-math-accordion-content">
		<div class="cmb-row cmb-type-select">
			<div class="cmb-row-col">
				<label for="site-adsense-account"><?php esc_html_e( 'Account', 'rank-math' ); ?></label>
				<select class="cmb2_select site-adsense-account notrack" name="site-adsense-account" id="site-adsense-account" data-selected="" disabled="disabled">
					<option value=""><?php esc_html_e( 'Select Account', 'rank-math' ); ?></option>
				</select>
			</div>
		</div>
		<div id="rank-math-pro-cta" class="no-margin">
			<div class="rank-math-cta-text">
				<span class="rank-math-pro-badge">
					<a href="<?php KB::the( 'pro', 'AdSense Toggle' ); ?>" target="_blank" rel="noopener noreferrer"><?php esc_html_e( 'PRO', 'rank-math' ); ?></a></span> <?php esc_html_e( "Google AdSense support is only available in Rank Math Pro's Advanced Analytics module.", 'rank-math' ); ?>
			</div>
		</div>
	</div>
</div>
<?php echo apply_filters( 'rank_math/analytics/adsense', ob_get_clean(), $analytics, $all_services ); // phpcs:ignore ?>

<div id="rank-math-pro-cta" class="rank-math-privacy-box width-100">
	<div class="rank-math-cta-table">
		<div class="rank-math-cta-body less-padding">
			<i class="dashicons dashicons-lock"></i>
			<p>
			<?php
			/* translators: %s: Link to KB article */
			printf( esc_html__( 'We do not store any of the data from your Google account on our servers, everything is processed & stored on your server. We take your privacy extremely seriously and ensure it is never misused. %s', 'rank-math' ), '<a href="' . KB::get( 'usage-policy', 'Analytics Privacy Notice' ) . '" target="_blank" rel="noopener noreferrer">' . esc_html__( 'Learn more.', 'rank-math' ) . '</a>' ); // phpcs:ignore
			?>
			</p>
		</div>
	</div>
</div>
<?php
// phpcs:enable

if ( Helper::is_wizard() && ! RankMath\Analytics\Email_Reports::are_fields_hidden() ) {
	?>
	<div class="cmb-row email-reports-header text-center" style="border-top:0;">
		<h1><?php esc_html_e( 'Email Reports', 'rank-math' ); ?></h1>
		<div class="email-reports-desc text-center"><?php esc_html_e( 'Receive Analytics reports periodically in email.', 'rank-math' ); ?> <a href="#" target="_blank"><?php esc_html_e( 'Learn more about Email Reports.', 'rank-math' ); ?></a></div>
	</div>
	<div class="cmb-row cmb-type-toggle cmb2-id-console-email-reports" data-fieldtype="toggle">
		<div class="cmb-th">
			<label for="console_email_reports"><?php esc_html_e( 'Email Reports', 'rank-math' ); ?></label>
		</div>
		<div class="cmb-td">
			<label class="cmb2-toggle"><input type="checkbox" class="regular-text" name="console_email_reports" id="console_email_reports" value="on" <?php checked( Helper::get_settings( 'general.console_email_reports' ) ); ?> data-hash="7e0rimtbvig0"><span class="cmb2-slider"><svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg><svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg></span></label>
		</div>
	</div>
	<?php
	do_action( 'rank_math/analytics/options/wizard_after_email_report' );
}
