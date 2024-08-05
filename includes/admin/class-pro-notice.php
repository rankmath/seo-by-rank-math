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

use RankMath\KB;
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
	 * Add inline JS & CSS related to the Pro notice.
	 *
	 * @return void
	 */
	public function pro_notice_js() {
		if ( ! Helper::has_notification( 'rank_math_pro_notice' ) ) {
			return;
		}
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
		<style>
			#rank_math_pro_notice.is-dismissible {
				background: #253142;
				color: #e4e5e7;
				border-width: 3px;
				border-style: solid;
				border-color: #161e28;
				padding: 0.25rem 1rem 1rem;
				border-radius: 5px;
			}
			#rank_math_pro_notice.is-dismissible p {
				font-size: 1.25rem;
				color: #f7d070;
				margin-bottom: 0;
			}
			#rank_math_pro_notice.is-dismissible ul {
				line-height: 1;
				margin-bottom: 0;
				text-align: left;
				opacity: 0.8;
				font-size: 15px;
				max-width: 530px;
			}
			#rank_math_pro_notice.is-dismissible li {
				display: inline-block;
				width: 49%;
				margin-bottom: 0.5rem;
			}
			#rank_math_pro_notice ul li:before {
				font-family: dashicons;
				font-size: 20px;
				width: 20px;
				height: 20px;
				margin-right: 5px;
				content: '\f147';
				text-align: center;
				vertical-align: middle;
				color: #161e28;
				border-radius: 10px;
				background: #9ce2b6;
			}
			#rank_math_pro_notice .button {
				border-color: #f7d070;
				background: #f7d070;
				color: #5a4000;
				font-size: 15px;
				margin-right: 12px;
			}
			div#rank_math_pro_notice .rank-math-maybe-later-action,
			div#rank_math_pro_notice .rank-math-already-upgraded-action {
				color: #f7d070;
				opacity: 0.7;
				margin: 0 12px;
				font-size: 13px;
			}

			.toplevel_page_rank-math #rank_math_pro_notice,
			body[class*="rank-math_page_rank-math-options-"] div#rank_math_pro_notice {
				display: none;
			}
		</style>
		<?php
	}

	/**
	 * Add admin notice.
	 *
	 * @param int $variant Notice variant.
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
				$message = '<p><strong>';

				$message .= esc_html__( 'Rank Your Content With the Power of PRO & A.I.', 'rank-math' );
				$message .= '</strong></p>';
				$message .= '<ul>
								<li>' . esc_html__( 'Unlimited Websites', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Content A.I. (Artificial Intelligence)', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Keyword Rank Tracker', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Powerful Schema Generator', 'rank-math' ) . '</li>
								<li>' . esc_html__( '24x7 Premium Support', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'SEO Email Reports', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'and Many More…', 'rank-math' ) . '</li>
							</ul>';
				$message .= '<p>
								<a href="' . KB::get( 'pro', 'Upgrade Notice 2 New Yes' ) . '" class="button rank-math-dismiss-pro-notice rank-math-upgrade-action" target="_blank" rel="noopener noreferrer"><strong>' . esc_html__( 'Yes, I want to learn more', 'rank-math' ) . '</strong></a><a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'No, I don\'t want it', 'rank-math' ) . '</a><a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'I already upgraded', 'rank-math' ) . '</a>
							</p>';
				break;

			default:
				$message = '<p><strong>';

				$message .= esc_html__( 'Rank Your Content With the Power of PRO & A.I.', 'rank-math' );
				$message .= '</strong></p><p>';
				$message .= '<ul>
								<li>' . esc_html__( 'Unlimited Websites', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Content A.I. (Artificial Intelligence)', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Keyword Rank Tracker', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'Powerful Schema Generator', 'rank-math' ) . '</li>
								<li>' . esc_html__( '24x7 Premium Support', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'SEO Email Reports', 'rank-math' ) . '</li>
								<li>' . esc_html__( 'and Many More…', 'rank-math' ) . '</li>
							</ul>';
				$message .= '<p>
						<a href="' . KB::get( 'pro', 'Upgrade Notice 1 New Yes' ) . '" class="button rank-math-dismiss-pro-notice rank-math-upgrade-action" target="_blank" rel="noopener noreferrer"><strong>' . esc_html__( 'Yes, I want better SEO', 'rank-math' ) . '</strong></a><a href="#" class="rank-math-dismiss-pro-notice rank-math-maybe-later-action">' . esc_html__( 'No, maybe later', 'rank-math' ) . '</a><a href="#" class="rank-math-dismiss-pro-notice rank-math-already-upgraded-action">' . esc_html__( 'I already purchased', 'rank-math' ) . '</a>
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
