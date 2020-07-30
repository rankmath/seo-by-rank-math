<?php
/**
 * Setup wizard ready step.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\Helper;
use RankMath\KB;
?>
<header>
	<h1>
		<i class="dashicons dashicons-yes"></i> <?php esc_html_e( 'Your site is ready!', 'rank-math' ); ?>
		<?php \RankMath\Admin\Admin_Helper::get_social_share(); ?>
	</h1>
</header>

<div class="rank-math-additional-options">
	<div class="rank-math-auto-update-wrapper">
		<h3><?php esc_html_e( 'Enable auto update of the plugin', 'rank-math' ); ?></h3>
		<span class="cmb2-toggle">
			<input type="checkbox" class="rank-math-modules" id="auto-update" value="" <?php checked( Helper::get_auto_update_setting() ); ?>  data-key="enable_auto_update" />
			<label for="auto-update" class="cmb2-slider">
				<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
				<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
			</label>
		</span>
	</div>

	<?php $style = ! Helper::get_auto_update_setting() ? 'style="display: none";' : ''; ?>
	<div class="rank-math-auto-update-email-wrapper" <?php echo $style; ?>>
		<h3><?php esc_html_e( 'Email me every time the plugin auto-updates.', 'rank-math' ); ?></h3>
		<span class="cmb2-toggle">
			<input type="checkbox" class="rank-math-modules" id="auto-update-email" value="" <?php checked( Helper::get_settings( 'general.enable_auto_update_email' ) ); ?> data-key="enable_auto_update_email" />
			<label for="auto-update-email" class="cmb2-slider">
				<svg width="3" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 2 6" class="toggle_on" role="img" aria-hidden="true" focusable="false"><path d="M0 0h2v6H0z"></path></svg>
				<svg width="8" height="8" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 6 6" class="toggle_off" role="img" aria-hidden="true" focusable="false"><path d="M3 1.5c.8 0 1.5.7 1.5 1.5S3.8 4.5 3 4.5 1.5 3.8 1.5 3 2.2 1.5 3 1.5M3 0C1.3 0 0 1.3 0 3s1.3 3 3 3 3-1.3 3-3-1.3-3-3-3z"></path></svg>
			</label>
		</span>
	</div>
</div>
<br class="clear">

<?php if ( ! Helper::is_whitelabel() ) : ?>

	<div class="wizard-next-steps wp-clearfix">
		<div class="score-100">
			<a href="<?php KB::the( 'score-100' ); ?>" target="_blank">
				<img src="<?php echo rank_math()->plugin_url(); ?>/assets/admin/img/score-100.png">
			</a>
		</div>
		<div class="learn-more">
			<h2><?php esc_html_e( 'Learn more', 'rank-math' ); ?></h2>
			<ul>
				<li>
					<span class="dashicons dashicons-facebook"></span><a href="<?php KB::the( 'fb-group' ); ?>" target="_blank"><strong><?php esc_html_e( 'Join FREE Facebook Group', 'rank-math' ); ?></strong></a>
				</li>
				<li>
					<span class="dashicons dashicons-welcome-learn-more"></span><a href="<?php KB::the( 'rm-kb' ); ?>" target="_blank"><?php esc_html_e( 'Rank Math Knowledge Base', 'rank-math' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-video-alt3"></span><a href="<?php KB::the( 'wp-error-fixes' ); ?>" target="_blank"><?php esc_html_e( 'Common WordPress Errors & Fixes', 'rank-math' ); ?></a>
				</li>
				<li>
					<span class="dashicons dashicons-sos"></span><a href="<?php KB::the( 'rm-support' ); ?>" target="_blank"><?php esc_html_e( 'Get 24x7 Support', 'rank-math' ); ?></a>
				</li>
			</ul>
		</div>
	</div>

	<footer class="form-footer wp-core-ui rank-math-ui">
		<a href="<?php echo esc_url( Helper::get_dashboard_url() ); ?>" class="button button-secondary rank-math-return-dashboard"><?php esc_html_e( 'Return to dashboard', 'rank-math' ); ?></a>
		<a href="<?php echo esc_url( Helper::get_admin_url( 'help' ) ); ?>" class="button button-secondary"><?php esc_html_e( 'Proceed to Help Page', 'rank-math' ); ?></a>
		<a href="<?php echo esc_url( $wizard->step_next_link() ); ?>" class="button button-primary rank-math-advanced-option"><?php esc_html_e( 'Setup Advanced Options', 'rank-math' ); ?></a>
		<?php do_action( 'rank_math/wizard/ready_footer', $wizard ); ?>
	</footer>
<?php else : ?>
	<p><?php esc_html_e( 'Your site is now optimized.', 'rank-math' ); ?></p>
	<footer class="form-footer wp-core-ui rank-math-ui">
		<a href="<?php echo esc_url( Helper::get_admin_url( 'options-general' ) ); ?>" class="button button-primary"><?php esc_html_e( 'Proceed to Settings', 'rank-math' ); ?></a>
	</footer>
	<?php
endif;
