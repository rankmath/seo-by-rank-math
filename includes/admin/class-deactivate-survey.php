<?php
/**
 * Handle the plugin deactivation feedback
 *
 * @since      1.0.3
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Runner;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Deactivate_Survey class.
 *
 * @codeCoverageIgnore
 */
class Deactivate_Survey implements Runner {

	use Hooker, Ajax;

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->action( 'admin_footer', 'deactivate_scripts' );
		$this->ajax( 'deactivate_feedback', 'deactivate_feedback' );
	}

	/**
	 * Send deactivation feedback to the Rank Math API.
	 */
	public function deactivate_feedback() {

		check_ajax_referer( 'rank_math_deactivate_feedback_nonce', 'security' );
		if ( ! current_user_can( 'activate_plugins' ) ) {
			$this->error( esc_html__( 'You are not authorized to perform this action.', 'rank-math' ) );
		}

		$reason_key  = Param::post( 'reason_key', '' );
		$reason_text = Param::post(
			"reason_{$reason_key}",
			$this->get_uninstall_reasons()[ $reason_key ]['title']
		);

		Api::get()->deactivate_feedback(
			[
				'product_slug'    => 'rank-math-suite',
				'product_name'    => 'Rank Math Suite',
				'product_version' => rank_math()->version,
				'feedback_key'    => $reason_key,
				'feedback'        => $reason_text,
			]
		);

		wp_send_json_success();
	}

	/**
	 * Output deactivate feedback popup.
	 */
	public function deactivate_scripts() {
		$screen = get_current_screen();

		// Early Bail!
		if ( empty( $screen ) || ! in_array( $screen->id, [ 'plugins', 'plugins-network' ], true ) ) {
			return;
		}

		// Scripts.
		rank_math()->admin_assets->enqueue_style( 'plugin-feedback' );
		rank_math()->admin_assets->enqueue_script( 'plugin-feedback' );

		// Form.
		?>
		<div class="rank-math-feedback-modal rank-math-ui rank-math-page" id="rank-math-feedback-form">
			<div class="rank-math-feedback-content">

				<header>

					<h2>
						<?php echo esc_html__( 'Help Us Improve', 'rank-math' ); ?>
						<span class="button-close dashicons dashicons-no-alt alignright"></span>
					</h2>

					<?php if ( true === apply_filters( 'rank_math_clear_data_on_uninstall', false ) ) { ?>
						<div class="rank-math-notice notice-alt notice-error">
							<p>
								<?php
								/* translators: 1. Bold text 2. Bold text */
								printf( esc_html__( '%1$s A filter to remove the Rank Math data from the database is present in your theme. Deactivating & Deleting this plugin will remove everything related to the Rank Math plugin. %2$s', 'rank-math' ), '<strong>CAUTION:</strong>', '<strong>This action is IRREVERSIBLE.</strong>' );
								?>
							</p>
						</div>
					<?php } ?>

					<p><?php echo esc_html__( 'Please share why you are deactivating Rank Math', 'rank-math' ); ?></p>

				</header>

				<form method="post">

					<input type="hidden" name="action" value="rank_math_deactivate_feedback" />
					<?php wp_nonce_field( 'rank_math_deactivate_feedback_nonce', 'security' ); ?>
					<div class="rank-math-feedback-options-wrapper">
						<?php foreach ( $this->get_uninstall_reasons() as $key => $reason ) : ?>
							<div class="rank-math-feedback-input-wrapper">

								<input id="deactivate-feedback-<?php echo esc_attr( $key ); ?>" type="radio" name="reason_key" value="<?php echo esc_attr( $key ); ?>" />

								<label for="deactivate-feedback-<?php echo esc_attr( $key ); ?>"><?php echo esc_html( $reason['title'] ); ?></label>

								<?php if ( ! empty( $reason['placeholder'] ) ) : ?>
									<input class="regular-text" type="text" name="reason_<?php echo esc_attr( $key ); ?>" placeholder="<?php echo esc_attr( $reason['placeholder'] ); ?>" />
								<?php endif; ?>

							</div>
						<?php endforeach; ?>
					</div>

					<footer>

						<button type="button" class="button button-link button-skip"><?php esc_html_e( 'Skip & Deactivate', 'rank-math' ); ?></button>

						<button type="submit" class="button button-primary alignright button-submit" disabled="disabled"><?php esc_html_e( 'Submit & Deactivate', 'rank-math' ); ?></button>

					</footer>

				</form>

			</div>

		</div>
		<?php
	}

	/**
	 * Get uninstall reasons.
	 *
	 * @return array
	 */
	private function get_uninstall_reasons() {
		return [
			'no_longer_needed'           => [
				'title'       => esc_html__( 'I no longer need the plugin', 'rank-math' ),
				'placeholder' => '',
			],
			'found_a_better_plugin'      => [
				'title'       => esc_html__( 'I found a better plugin', 'rank-math' ),
				'placeholder' => esc_html__( 'Please share which plugin', 'rank-math' ),
			],
			'couldnt_get_plugin_to_work' => [
				'title'       => esc_html__( 'I couldn\'t get the plugin to work', 'rank-math' ),
				'placeholder' => '',
			],
			'temporary_deactivation'     => [
				'title'       => esc_html__( 'It\'s a temporary deactivation', 'rank-math' ),
				'placeholder' => '',
			],
			'other'                      => [
				'title'       => esc_html__( 'Other', 'rank-math' ),
				'placeholder' => esc_html__( 'Please share the reason', 'rank-math' ),
			],
		];
	}
}
