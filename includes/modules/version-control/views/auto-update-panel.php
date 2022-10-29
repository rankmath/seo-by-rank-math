<?php
/**
 * Auto Updater view in Version Control Tab.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

defined( 'ABSPATH' ) || exit;

?>
<form id="cmb2-metabox-rank-math-auto-update" class="rank-math-auto-update-form cmb2-form rank-math-box" action="" method="post">

	<header>
		<h3><?php esc_html_e( 'Auto Update', 'rank-math' ); ?></h3>
	</header>

	<p><?php esc_html_e( 'Turn on auto-updates to automatically update to stable versions of Rank Math as soon as they are released. The beta versions will never install automatically.', 'rank-math' ); ?></p>

	<table class="form-table">
		<tbody>
			<tr class="cmb-row cmb-type-switch">
				<th scope="row"><label><?php esc_html_e( 'Auto Update Plugin', 'rank-math' ); ?></label></th>
				<td>
					<label class="cmb2-toggle">
						<input type="hidden" name="enable_auto_update" id="enable_auto_update_hidden" value="off">
						<input type="checkbox" class="regular-text" name="enable_auto_update" id="enable_auto_update" value="on" <?php checked( $auto_update ); ?>>
						<span class="cmb2-slider">
							<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
							<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
						</span>
					</label>
				</td>
			</tr>
		</tbody>
	</table>

	<div id="control_update_notification_email">
		<p><?php esc_html_e( 'When auto-updates are turned off, you can enable update notifications, to send an email to the site administrator when an update is available for Rank Math.', 'rank-math' ); ?></p>

		<table class="form-table">
			<tbody>
				<tr class="cmb-row cmb-type-switch">
					<th scope="row"><label><?php esc_html_e( 'Update Notification Email', 'rank-math' ); ?></label></th>
					<td>
						<label class="cmb2-toggle">
							<input type="hidden" name="enable_update_notification_email" id="enable_update_notification_email_hidden" value="off">
							<input type="checkbox" class="regular-text" name="enable_update_notification_email" id="enable_update_notification_email" value="on" <?php checked( $update_notification ); ?>>
							<span class="cmb2-slider">
								<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
								<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
							</span>
						</label>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<?php if ( get_option( 'rank_math_rollback_version', false ) ) { ?>
		<div class="notice notice-alt notice-warning info inline" style="border: none;">
			<p>
				<?php esc_html_e( 'Rank Math will not auto-update because you have rolled back to a previous version. Update to the latest version manually to make this option work again.', 'rank-math' ); ?>
			</p>
		</div>
	<?php } ?>

	<footer>
		<?php wp_nonce_field( 'rank-math-auto-update' ); ?>
		<button type="submit" class="button button-primary button-xlarge"><?php esc_html_e( 'Save Changes', 'rank-math' ); ?></button>
	</footer>

</form>
