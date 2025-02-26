<?php
/**
 * Ask user to review Rank Math on wp.org, in the meta box after 2 weeks.
 *
 * @since      0.9.0
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
 * Ask_Review class.
 */
class Ask_Review {

	use Hooker;
	use Ajax;

	/**
	 * Now.
	 *
	 * @var string
	 */
	public $current_time = '';

	/**
	 * Date of release of version 1.0.57. Turned into a timestamp in the constructor.
	 *
	 * @var string
	 */
	public $record_date = '2021-02-03 13:00';

	/**
	 * Rank Math plugin install date.
	 *
	 * @var string
	 */
	public $install_date = '';

	/**
	 * Constructor method.
	 */
	public function __construct() {
		$this->current_time = Helper::get_current_time();
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
		$this->ajax( 'already_reviewed', 'already_reviewed' );

		// Post editor tab.
		if ( $this->current_time > $this->install_date + ( 10 * DAY_IN_SECONDS ) ) {
			Helper::add_json( 'showReviewTab', true );
		}

		// Admin notice.
		$review_notice_date = $this->get_review_notice_date();
		if ( $this->current_time > $review_notice_date ) {
			if ( get_option( 'rank_math_review_notice_added' ) === false && ! Helper::has_notification( 'rank_math_pro_notice' ) ) {
				$this->add_notice();
			}

			// Make dismiss button work like the "Maybe later" link.
			$this->action( 'wp_helpers_notification_dismissed', 'review_notice_after_dismiss' );
		}

		$this->action( 'admin_footer', 'review_notice_js', 15 );
	}

	/**
	 * Add inline JS related to the review notice.
	 *
	 * @return void
	 */
	public function review_notice_js() {
		if ( ! Helper::has_notification( 'rank_math_review_plugin_notice' ) ) {
			return;
		}
		?>
		<script>
			(function( $ ) {
				$( function() {
					$('.rank-math-dismiss-review-notice').on( 'click', function(e) {
						var $this = $(this);

						if ( ! $this.hasClass( 'rank-math-review-action' ) ) {
							e.preventDefault();
						}

						if ( $this.hasClass( 'rank-math-maybe-later-action' ) ) {
							$('#rank_math_review_plugin_notice').find( '.notice-dismiss' ).trigger('click');
							return false;
						}

						jQuery.ajax( {
							url: rankMath.ajaxurl,
							data: { action: 'rank_math_already_reviewed', security: rankMath.security,
							},
						} );

						$('#rank_math_review_plugin_notice').find( '.notice-dismiss' ).trigger('click');
					});
				});
			})(jQuery);
		</script>
		<style>
			#rank_math_review_plugin_notice .rank-math-notice.is-dismissible a,
			#rank_math_pro_notice .rank-math-notice.is-dismissible a {
				color: #4f52d4;
			}
			#rank_math_review_plugin_notice.is-dismissible,
			#rank_math_pro_notice.is-dismissible {
				border-width: 0 0 0 4px;
				border-left-color: #6668BD;
				box-shadow: 0 1px 4px rgba(0, 0, 0, 0.15);
				padding: 5px 10px 5px 65px;
			}
			#rank_math_review_plugin_notice.is-dismissible:before,
			#rank_math_pro_notice.is-dismissible:before {
				content: '';
				width: 50px;
				height: 100%;
				background: rgba(102, 104, 189, 0.09);
				position: absolute;
				left: 0;
				top: 0;
			}
			#rank_math_review_plugin_notice.is-dismissible:after,
			#rank_math_pro_notice.is-dismissible:after {
				content: url('data:image/svg+xml;charset=UTF-8, <svg viewBox="0 0 462.03 462.03" xmlns="http://www.w3.org/2000/svg" width="20"><g fill="white"><path d="m462 234.84-76.17 3.43 13.43 21-127 81.18-126-52.93-146.26 60.97 10.14 24.34 136.1-56.71 128.57 54 138.69-88.61 13.43 21z"></path><path d="m54.1 312.78 92.18-38.41 4.49 1.89v-54.58h-96.67zm210.9-223.57v235.05l7.26 3 89.43-57.05v-181zm-105.44 190.79 96.67 40.62v-165.19h-96.67z"></path></g></svg>' );
				padding: 3px;
				border-radius: 3px;
				position: absolute;
				left: 12px;
				top: 18px;
				background: linear-gradient(-135deg, #2488e1, #724bb7);
				width: 23px;
				height: 23px;
				display: flex;
				justify-content: center;
				line-height: 1;
				align-items: center;
			}
		</style>
		<?php
	}

	/**
	 * Add admin notice.
	 *
	 * @return void
	 */
	public function add_notice() {
		$message = '<p>';

		// Translators: placeholder is the plugin name.
		$message .= sprintf( esc_html__( 'Hey, we noticed you\'ve been using %s for more than a week now – that\'s awesome!', 'rank-math' ), '<strong>' . _x( 'Rank Math SEO', 'plugin name inside the review notice', 'rank-math' ) . '</strong>' );
		$message .= '<br>';

		$message .= esc_html__( 'Could you please do us a BIG favor and give it a rating on WordPress.org to help us spread the word and boost our motivation?', 'rank-math' ) . '</p>
			<p><strong>Bhanu Ahluwalia</strong><br>' . esc_html__( 'Co-founder of Rank Math', 'rank-math' ) . '</p>
			<p>
				<a href="https://wordpress.org/support/plugin/seo-by-rank-math/reviews/?filter=5#new-post" class="rank-math-dismiss-review-notice rank-math-review-action rank-math-review-out" target="_blank" rel="noopener noreferrer"><strong>' . esc_html__( 'Yes, you deserve it', 'rank-math' ) . '</strong></a><br>
				<a href="#" class="rank-math-dismiss-review-notice rank-math-maybe-later-action">' . esc_html__( 'No, maybe later', 'rank-math' ) . '</a><br>
				<a href="#" class="rank-math-dismiss-review-notice rank-math-already-reviewed-action">' . esc_html__( 'I already did', 'rank-math' ) . '</a>
			</p>';

		Helper::add_notification(
			$message,
			[
				'type'       => 'info',
				'id'         => 'rank_math_review_plugin_notice',
				'capability' => 'install_plugins',
			]
		);

		update_option( 'rank_math_review_notice_added', '1', false );
	}

	/**
	 * Set "already reviewed" flag after the user dismisses the notice.
	 *
	 * @param string $notification_id Dismissed notice ID.
	 * @return void
	 */
	public function review_notice_after_dismiss( $notification_id ) {
		if ( 'rank_math_review_plugin_notice' !== $notification_id ) {
			return;
		}

		delete_option( 'rank_math_review_notice_date' );
		delete_option( 'rank_math_review_notice_added' );
		update_option( 'rank_math_review_notice_delayed', true, false );
	}

	/**
	 * Get stored notice start date.
	 *
	 * @return int
	 */
	public function get_review_notice_date() {
		$review_notice_date = get_option( 'rank_math_review_notice_date' );
		if ( false !== $review_notice_date ) {
			return $review_notice_date;
		}

		$delay_days = 14;
		if ( $this->install_date < $this->record_date && ! get_option( 'rank_math_review_notice_delayed' ) ) {
			$delay_days = wp_rand( 7, 30 );
		}

		$review_notice_date = $this->current_time + ( $delay_days * DAY_IN_SECONDS );
		update_option( 'rank_math_review_notice_date', $review_notice_date, false );

		return $review_notice_date;
	}

	/**
	 * Set "already reviewed" flag.
	 */
	public function already_reviewed() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );
		update_option( 'rank_math_already_reviewed', Helper::get_current_time() );
		$this->success( 'success' );
	}
}
