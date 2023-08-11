<?php
/**
 * Beta Opt-in view in Version Control Tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;
?>

<form id="cmb2-metabox-rank-math-beta-optin" class="rank-math-beta-optin-form cmb2-form rank-math-box" action="" method="post">

	<header>
		<h3><?php esc_html_e( 'Beta Opt-in', 'rank-math' ); ?></h3>
	</header>

	<?php if ( Helper::is_plugin_update_disabled() ) : ?>
		<p><?php esc_html_e( 'You cannot turn on the Beta Tester feature because site wide plugins auto-update option is disabled on your site.', 'rank-math' ); ?></p>

</form>
		<?php return; ?>
	<?php endif; ?>

	<p><?php esc_html_e( 'You can take part in shaping Rank Math by test-driving the newest features and letting us know what you think. Turn on the Beta Tester feature to get notified about new beta releases. The beta version will not install automatically and you always have the option to ignore it.', 'rank-math' ); ?></p>
	<?php // translators: Warning. ?>
	<p class="description warning"><strong><?php printf( esc_html__( '%s It is not recommended to use the beta version on live production sites.', 'rank-math' ), '<span class="warning">' . esc_html__( 'Warning: ', 'rank-math' ) . '</span>' ); ?></strong></p>

	<table class="form-table">
		<tbody>
			<tr class="cmb-row cmb-type-switch">
				<th scope="row"><label><?php esc_html_e( 'Beta Tester', 'rank-math' ); ?></label></th>
				<td>
					<label class="cmb2-toggle">
						<input type="hidden" name="beta_optin" id="beta_optin_hidden" value="off">
						<input type="checkbox" class="regular-text" name="beta_optin" id="beta_optin" value="on" <?php checked( $beta_optin ); ?>>
						<span class="cmb2-slider">
							<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
							<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
						</span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>

	<footer>
		<?php wp_nonce_field( 'rank-math-beta-optin' ); ?>
		<button type="submit" class="button button-primary button-xlarge"><?php esc_html_e( 'Save Changes', 'rank-math' ); ?></button>
	</footer>

</form>
