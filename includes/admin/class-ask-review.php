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
	 * Register hooks.
	 */
	public function hooks() {
		Helper::add_json( 'showReviewTab', true );
		$this->ajax( 'already_reviewed', 'already_reviewed' );
		$this->filter( 'rank_math/metabox/tabs', 'add_metabox_tab' );
		$this->filter( 'admin_footer_text', 'admin_footer_text', 20 );
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

			<h3><?php _e( 'Rate Rank Math SEO', 'rank-math' ); ?></h3>

			<p>
				<?php _e( 'Hey, we noticed you are using Rank Math SEO plugin for more than 2 weeks – <em>that\'s awesome!</em> <br>Could you please do us a BIG favor and give it a rating on WordPress to help us spread the word and boost our motivation?', 'rank-math' ); ?>
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
					<?php _e( 'I already did. Please don\'t show this message again.', 'rank-math' ); ?>
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
