<?php
/**
 * Shared status cards ("Connect your Rank Math account", "Rank Math Assistant Unavailable") shown by any WAP agent persona in place of the chat widget.
 *
 * @since      1.0.282
 * @package    RankMath
 * @subpackage RankMath\Agent
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

use RankMath\KB;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

/**
 * Connect_Cta trait.
 */
trait Connect_Cta {

	/**
	 * Echo the "Connect your Rank Math account" CTA.
	 *
	 * @param string $description Body copy explaining what connecting unlocks for this persona.
	 * @param string $page_slug   Menu slug to return to once Connect completes.
	 */
	protected function render_connect_cta( $description, $page_slug ) {
		$activate_url   = Admin_Helper::get_activate_url( admin_url( 'admin.php?page=' . $page_slug ) );
		$site_url_valid = Admin_Helper::is_site_url_valid();
		$button_class   = 'rank-math-button components-button button-animate' . ( $site_url_valid ? '' : ' disabled' );
		$ns             = 'rank-math-ai-visibility-account';
		?>
		<div class="rank-math-wap-connect-cta">
			<div class="<?php echo esc_attr( $ns ); ?> <?php echo esc_attr( $ns ); ?>-disconnected">
				<header>
					<h3><?php esc_html_e( 'Account Connection Required', 'seo-by-rank-math' ); ?></h3>
					<button type="button" class="rank-math-status-button components-button is-disconnected" disabled="disabled">
						<span class="dashicons dashicons-no-alt"></span>
						<?php esc_html_e( 'Not Connected', 'seo-by-rank-math' ); ?>
					</button>
				</header>

				<div class="<?php echo esc_attr( $ns ); ?>-content">
					<div>
						<p><?php echo esc_html( $description ); ?></p>

						<?php Admin_Helper::maybe_show_invalid_siteurl_notice(); ?>

						<a href="<?php echo esc_url( $activate_url ); ?>" class="<?php echo esc_attr( $button_class ); ?>">
							<?php esc_html_e( 'Connect Now', 'seo-by-rank-math' ); ?>
						</a>

						<p class="<?php echo esc_attr( $ns ); ?>-not-registered-note"><?php esc_html_e( 'Takes less than 30 seconds to get started', 'seo-by-rank-math' ); ?></p>

						<p class="<?php echo esc_attr( $ns ); ?>-kb-link">
							<a href="<?php echo esc_url( KB::get( 'knowledgebase', 'Assistant Connect CTA' ) ); ?>" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Browse help articles', 'seo-by-rank-math' ); ?>
							</a>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}

	/**
	 * Echo the "Rank Math Assistant Unavailable" card when WordPress Application Passwords
	 * cannot be used due to HTTPS or being disabled by the site.
	 */
	protected function render_unavailable_cta() {
		$https_missing = \GroupOne\WapClient\AppPasswordManager::is_https_missing();
		$ns            = 'rank-math-ai-visibility-account';

		if ( $https_missing ) {
			$description = __( 'Rank Math Assistant requires HTTPS to use WordPress Application Passwords. It will be available once this site is served over HTTPS.', 'seo-by-rank-math' );
		} else {
			$description = __( 'Rank Math Assistant is unavailable because WordPress Application Passwords have been disabled on this site — for example, by a security plugin. Enable Application Passwords to use the assistant.', 'seo-by-rank-math' );
		}
		?>
		<div class="rank-math-wap-connect-cta">
			<div class="<?php echo esc_attr( $ns ); ?> <?php echo esc_attr( $ns ); ?>-unavailable">
				<header>
					<h3><?php esc_html_e( 'Rank Math Assistant Unavailable', 'seo-by-rank-math' ); ?></h3>
					<button type="button" class="rank-math-status-button components-button is-unavailable" disabled="disabled">
						<span class="dashicons dashicons-warning"></span>
						<?php esc_html_e( 'Unavailable', 'seo-by-rank-math' ); ?>
					</button>
				</header>

				<div class="<?php echo esc_attr( $ns ); ?>-content">
					<div>
						<p>
							<?php echo esc_html( $description ); ?>
							<a href="https://make.wordpress.org/core/2020/11/05/application-passwords-integration-guide/" target="_blank" rel="noopener noreferrer">
								<?php esc_html_e( 'Learn more', 'seo-by-rank-math' ); ?>
							</a>
						</p>

						<p>
							<?php
							echo wp_kses_post(
								sprintf(
									/* translators: %s: Rank Math Knowledge Base URL */
									__( 'Need help? Visit our <a href="%s" target="_blank" rel="noopener noreferrer">Knowledge Base</a> for support.', 'seo-by-rank-math' ),
									esc_url( KB::get( 'knowledgebase', 'Sidebar Help Link' ) )
								)
							);
							?>
						</p>
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
