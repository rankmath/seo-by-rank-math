<?php
/**
 * Shared "Connect your Rank Math account" CTA, shown by any WAP agent persona in place of the chat widget when not connected.
 *
 * @since      1.0.277
 * @package    RankMath
 * @subpackage RankMath\Agent
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Traits;

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
					</div>
				</div>
			</div>
		</div>
		<?php
	}
}
