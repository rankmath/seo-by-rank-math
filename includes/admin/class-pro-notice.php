<?php
/**
 * Inform the user about Rank Math PRO after 20 days of usage.
 *
 * @since      1.0.69
 * @package    RankMath
 * @subpackage RankMath\Admin
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Admin;

use RankMath\Helper;
use RankMath\Traits\Ajax;
use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Pro_Notice class.
 */
class Pro_Notice {

	use Hooker, Ajax;

	/**
	 * Now.
	 *
	 * @var string
	 */
	public $current_time = '';

	/**
	 * Rank Math plugin install date.
	 *
	 * @var string
	 */
	public $install_date = '';

	/**
	 * Date of release of version 1.0.69. Turned into a timestamp in the constructor.
	 *
	 * @var string
	 */
	public $record_date = '2021-07-30 13:00';

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->current_time = current_time( 'timestamp' );
		$this->record_date  = strtotime( $this->record_date );
		$this->install_date = get_option( 'rank_math_install_date' );
		if ( false === $this->install_date ) {
			$this->install_date = $this->current_time;
		}
	}

	/**
	 * Register hooks.
	 */
	public function hooks() {
		$this->ajax( 'dismiss_pro_notice', 'dismiss' );

		// Admin notice.
		$notice_date = $this->get_notice_date();
		if ( $this->current_time > $notice_date ) {
			if ( get_option( 'rank_math_pro_notice_added' ) === false && ! Helper::has_notification( 'rank_math_review_plugin_notice' ) ) {
				$this->add_notice( (int) get_option( 'rank_math_pro_notice_delayed' ) );
			}

			// Make dismiss button work like the "Maybe later" link.
			$this->action( 'wp_helpers_notification_dismissed', 'pro_notice_after_dismiss' );

			$this->action( 'admin_footer', 'pro_notice_js', 15 );
		}
	}

	/**
	 * Add inline JS related to the Pro notice.
	 *
	 * @return void
	 */
	public function pro_notice_js() {
		?>
		<script>
			(function( $ ) {
				$( function() {
					$('.rank-math-dismiss-pro-notice').on( 'click', function(e) {
						var $this = $(this);

						if ( ! $this.hasClass( 'rank-math-upgrade-action' ) ) {
							e.preventDefault();
						}

						if ( $this.hasClass( 'rank-math-maybe-later-action' ) ) {
							$('#rank_math_pro_notice').find( '.notice-dismiss' ).trigger('click');
							return false;
						}

						jQuery.ajax( {
							url: rankMath.ajaxurl,
							data: { action: 'rank_math_already_upgraded', security: rankMath.security,
							},
						} );

						$('#rank_math_pro_notice').find( '.notice-dismiss' ).trigger('click');
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Add admin notice.
	 *
	 * @return void
	 */
	public function add_notice( $variant = 0 ) {
		$message = $this->get_notice_text( $variant );

		Helper::add_notification(
			$message,
			[
				'type'       => 'info',
				'id'         => 'rank_math_pro_notice',
				'capability' => 'install_plugins',
			]
		);

		update_option( 'rank_math_pro_notice_added', '1', false );
	}

	/**
	 * Get notice texts.
	 *
	 * @param integer $variant Message variant.
	 * @return string
	 */
	public function get_notice_text( $variant = 0 ) {
		$message = '';
		switch ( (int) $variant ) {
			case 1:
				$message = '<p>';

				// Translators: placeholders are the opening and closing strong tags.
				$message .= sprintf( esc_html__( 'Hey, you know the %1$s secret of success of Rank Math? It\'s your success! %2$s When you succeed, we succeed as well.', 'rank-math' ), '<strong>', '</strong>' );
				$message .= '</p><p>';
				// Translators: placeholder is the words "check here".
				$message .= sprintf( esc_html__( 'You are missing on the easy SEO traffic to your website. %s why Rank Math PRO is the last SEO tool that you\'ll ever buy!', 'rank-math' ), '<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Upgrade%20Notice%202%20Pricing%20Link&utm_campaign=WP" target="_blank"><strong>' . __( 'CHECK HERE', 'rank-math' ) . '</strong></a>' );
				$message .= '</p><p>';
				$message .= sprintf( esc_html__( 'It has everything that is needed to optimize your website for more traffic from the search engines.', 'rank-math' ) );

				$message .= '</p>
					<p><strong>Bhanu Ahluwalia</strong><br>' . esc_html__( 'Co-founder of Rank Math', 'rank-math' ) . '</p>
					<p>
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Upgrade%20Notice%202%20Yes&utm_campaign=WP" class="rank-math-dismiss-pro-notice rank-math-upgrade-action" target="_blank" rel="noopener noreferrer"><strong>' . esc_html__( 'Yes, I want to learn more', 'rank-math' ) . '</strong></a><br>
						<a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'No, I don\'t want it', 'rank-math' ) . '</a><br>
						<a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'I already upgraded', 'rank-math' ) . '</a>
					</p>';
				break;

			default:
				$message = '<p>';

				// Translators: placeholder is the plugin name.
				$message .= sprintf( esc_html__( 'Hey, did you know that with %1$s, %2$syou can drive more traffic%3$s by easily automating your SEO tasks?', 'rank-math' ), '<a href="https://rankmath.com/free-vs-pro/?utm_source=Plugin&utm_medium=Upgrade%20Notice%201%20Free%20vs%20PRO&utm_campaign=WP" target="_blank">' . _x( 'Rank Math PRO', 'Pro plugin name inside the admin notice', 'rank-math' ) . '</a>', '<strong>', '</strong>' );
				$message .= '<br>';
				// Translators: placeholder is the words "buy here".
				$message .= sprintf( esc_html__( '%s at a discounted price.', 'rank-math' ), '<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Upgrade%20Notice%201%20Pricing%20Link&utm_campaign=WP" target="_blank"><strong>' . __( 'BUY HERE', 'rank-math' ) . '</strong></a>' );

				$message .= '</p>
					<p><strong>Bhanu Ahluwalia</strong><br>' . esc_html__( 'Co-founder of Rank Math', 'rank-math' ) . '</p>
					<p>
						<a href="https://rankmath.com/pricing/?utm_source=Plugin&utm_medium=Upgrade%20Notice%201%20Yes&utm_campaign=WP" class="rank-math-dismiss-pro-notice rank-math-upgrade-action" target="_blank" rel="noopener noreferrer"><strong>' . esc_html__( 'Yes, I want better SEO', 'rank-math' ) . '</strong></a><br>
						<a href="#" class="rank-math-dismiss-pro-notice rank-math-maybe-later-action">' . esc_html__( 'No, maybe later', 'rank-math' ) . '</a><br>
						<a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'I already purchased', 'rank-math' ) . '</a>
					</p>';
				break;
		}

		return $message;
	}

	/**
	 * Set "delayed" flag after the user dismisses the notice.
	 *
	 * @param string $notification_id Dismissed notice ID.
	 * @return void
	 */
	public function pro_notice_after_dismiss( $notification_id ) {
		if ( 'rank_math_pro_notice' !== $notification_id ) {
			return;
		}

		// If it has already been delayed once then dismiss it forever.
		if ( get_option( 'rank_math_pro_notice_delayed' ) ) {
			update_option( 'rank_math_already_upgraded', current_time( 'timestamp' ) );
			return;
		}

		delete_option( 'rank_math_pro_notice_date' );
		delete_option( 'rank_math_pro_notice_added' );
		update_option( 'rank_math_pro_notice_delayed', 1, false );
	}

	/**
	 * Get stored notice start date.
	 *
	 * @return int
	 */
	public function get_notice_date() {
		$notice_date = get_option( 'rank_math_pro_notice_date' );
		if ( false !== $notice_date ) {
			return $notice_date;
		}

		$delay_days = 10;
		if ( $this->install_date < $this->record_date && ! get_option( 'rank_math_pro_notice_delayed' ) ) {
			$delay_days = wp_rand( 7, 30 );
		}

		$notice_date = $this->current_time + ( $delay_days * DAY_IN_SECONDS );
		update_option( 'rank_math_pro_notice_date', $notice_date, false );

		return $notice_date;
	}

	/**
	 * Set the "already upgraded" flag.
	 * This also sets the "already reviewed" flag, so the review notice will not show up anymore either.
	 */
	public function dismiss() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );

		update_option( 'rank_math_already_upgraded', current_time( 'timestamp' ) );
		update_option( 'rank_math_already_reviewed', current_time( 'timestamp' ) );

		$this->success( 'success' );
	}
}
