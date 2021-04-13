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
use MyThemeShop\Helpers\Arr;
use MyThemeShop\Helpers\Param;

defined( 'ABSPATH' ) || exit;

/**
 * Ask_Review class.
 */
class Ask_Review {

	use Hooker, Ajax;

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
		$this->ajax( 'already_reviewed', 'already_reviewed' );

		// Post editor tab.
		if ( $this->current_time > $this->install_date + ( 2 * WEEK_IN_SECONDS ) ) {
			Helper::add_json( 'showReviewTab', true );
			$this->filter( 'rank_math/metabox/tabs', 'add_metabox_tab' );
			$this->filter( 'admin_footer_text', 'admin_footer_text', 20 );
		}

		// Admin notice.
		$review_notice_date = $this->get_review_notice_date();
		if ( $this->current_time > $review_notice_date ) {
			if ( get_option( 'rank_math_review_notice_added' ) === false ) {
				$this->add_notice();
			}

			// Make dismiss button work like the "Maybe later" link.
			$this->action( 'wp_helpers_notification_dismissed', 'review_notice_after_dismiss' );

			// Inlinde notice related JS.
			$this->action( 'admin_footer', 'review_notice_js', 15 );
		}
	}

	/**
	 * Add inline JS related to the review notice.
	 *
	 * @return void
	 */
	public function review_notice_js() {
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
		$message .= sprintf( esc_html__( 'Hey, we noticed you\'ve been using %s for more than two weeks now – that\'s awesome!', 'rank-math' ), '<strong>' . _x( 'Rank Math SEO', 'plugin name inside the review notice', 'rank-math' ) . '</strong>' );
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
	 * Add footer credit on admin pages.
	 *
	 * @param string $text Default text for admin footer.
	 * @return string
	 */
	public function admin_footer_text( $text ) {
		if ( substr( Param::get( 'page' ), 0, 9 ) !== 'rank-math' ) {
			return $text;
		}

		if ( Helper::is_whitelabel() ) {
			return $text;
		}

		// Add dismiss JS.
		$this->filter( 'admin_footer', 'print_footer_script', 20 );

		$star  = '<i class="rm-icon rm-icon-star-filled"></i>';
		$stars = '<a href="https://wordpress.org/support/plugin/seo-by-rank-math/reviews/#new-post" target="_blank" style="color:#FF9800;font-size:9px;text-decoration:none;letter-spacing:2px;">' . str_repeat( $star, 5 ) . '</a>';

		/* translators: placeholder is a wp.org review link */
		$new_text = sprintf( esc_html__( 'If you like Rank Math, please take a minute to rate it on WordPress.org: %s', 'rank-math' ), $stars );

		return '<span id="rank-math-footer-ask-review" data-original-text="' . esc_attr( $text ) . '">' . $new_text . '</span>';
	}

	/**
	 * Add rich snippet tab to the metabox.
	 *
	 * @param array $tabs Array of tabs.
	 *
	 * @return array
	 */
	public function add_metabox_tab( $tabs ) {
		Arr::insert(
			$tabs,
			[
				'askreview' => [
					'icon'       => 'rm-icon rm-icon-heart-filled',
					'title'      => '',
					'desc'       => '',
					'file'       => rank_math()->includes_dir() . 'metaboxes/ask-review.php',
					'capability' => 'onpage_general',
				],
			],
			11
		);

		return $tabs;
	}

	/**
	 * Set "already reviewed" flag.
	 */
	public function already_reviewed() {
		check_ajax_referer( 'rank-math-ajax-nonce', 'security' );
		$this->has_cap_ajax( 'onpage_general' );
		update_option( 'rank_math_already_reviewed', current_time( 'timestamp' ) );
		$this->success( 'success' );
	}

	/**
	 * Display tab content.
	 */
	public static function display() {
		ob_start();
		?>
		<div class="ask-review">

			<h3><?php esc_html_e( 'Rate Rank Math SEO', 'rank-math' ); ?></h3>

			<p>
				<?php echo wp_kses_post( __( 'Hey, we noticed you\'ve been using Rank Math SEO for more than two weeks now – <em>that\'s awesome!</em> <br>Could you please do us a BIG favor and give it a rating on WordPress to help us spread the word and boost our motivation?', 'rank-math' ) ); ?>
			</p>

			<div class="stars-wrapper">

				<div class="face">
					<div class="smiley happy">
						<div class="eyes">
							<div class="eye"></div>
							<div class="eye"></div>
						</div>
						<div class="mouth"></div>
					</div>
				</div>

				<div class="stars">
					<?php for ( $i = 1; $i <= 5; $i++ ) { ?>
						<a href="https://s.rankmath.com/wpreview" target="_blank">
							<span class="dashicons dashicons-star-filled"></span>
						</a>
					<?php } ?>
				</div>

			</div>

			<label>

				<input type="checkbox" id="already-reviewed" />

				<span>
					<?php esc_html_e( 'I already did. Please don\'t show this message again.', 'rank-math' ); ?>
				</span>

			</label>

		</div>
		<?php
		self::print_script();

		return ob_get_clean();
	}

	/**
	 * Print javascript
	 */
	public static function print_script() {
		?>
		<script>
			(function( $ ) {
				$( function() {
					var rating_wrapper  = $( '#setting-panel-askreview' ),
						rating_stars    = rating_wrapper.find( '.stars a' ),
						rating_smiley   = rating_wrapper.find( '.smiley' ),
						rating_contents = rating_wrapper.find( '.ask-review' );

					rating_stars.on( 'mouseenter', function() {
						var pos = $( this ).index();

						rating_stars.removeClass( 'highlighted' );
						rating_stars.slice( 0, pos + 1 ).addClass( 'highlighted' );

						if ( pos < 2 ) {
							rating_smiley.removeClass( 'normal happy' ).addClass( 'angry' );
						} else if ( pos > 3 ) {
							rating_smiley.removeClass( 'normal angry' ).addClass( 'happy' );
						} else {
							rating_smiley.removeClass( 'happy angry' ).addClass( 'normal' );
						}
					});

					$( '#already-reviewed' ).change(function() {
						$.ajax({
							url: ajaxurl,
							data: {
								action: 'rank_math_already_reviewed',
								security: rankMath.security,
							},
						});
						rating_contents.animate({
							opacity: 0.01
						}, 1500, function() {
							$( '.rank-math-tabs-navigation > a' ).first().click();
							$( '.rank-math-tabs-navigation' ).children( '[href = "#setting-panel-askreview"]' ).remove();
						});
					});
				});
			})(jQuery);
		</script>
		<?php
	}

	/**
	 * Print javascript for footer notice dismiss functionality.
	 */
	public static function print_footer_script() {
		?>
		<script>
			(function( $ ) {
				$( function() {
					var rating_wrapper  = $( '#rank-math-footer-ask-review' );

					$( 'a', rating_wrapper ).on( 'mousedown', function() {
						$.ajax({
							url: ajaxurl,
							data: {
								action: 'rank_math_already_reviewed',
								security: rankMath.security,
							},
						});
						rating_wrapper.animate({
							opacity: 0.01
						}, 1500, function() {
							rating_wrapper.html( rating_wrapper.data('original-text') ).css( 'opacity', '1' );
						});
					});
				});
			})(jQuery);
		</script>
		<?php
	}
}
