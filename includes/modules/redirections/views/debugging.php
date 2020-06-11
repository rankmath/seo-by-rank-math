<?php
/**
 * Redirection debugging template.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

use RankMath\Helper;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title>Debugging SEO Redirection</title>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
	<style type="text/css">
		a {
			color: #3a3aea;
			text-decoration: none;
		}
		.rank-math-redrection-debug {
			width: 540px;
			margin: 0 auto;
			padding: 14px 16px;
			margin-top: 20px;
			border: 2px solid #f8dd91;
			background: #fef7e6;
			color: #343434;
			font-family: Arial;
			text-align: center;
		}

		.rank-math-redirection-loading-animation {
			margin-bottom: 8px;
		}

		.rank-math-redirection-loading-timer {
			display: inline-block;
			margin-top: 0;
			vertical-align: 12px;
			margin-left: 14px;
		}
		.redirection-timer {
			display: inline-block;
			margin: 6px 10px 0 0;
		}
		code {
			font-family: Consolas, Monaco, monospace;
			unicode-bidi: embed;
			padding: 3px 5px 2px 5px;
			margin: 0 1px;
			background-color: #f7f7f9;
			border: 1px solid #e1e1e8;
			font-size: 13px;
			color: #d72b3f;
		}

		code a {
			color: #d72b3f;
		}
		.rank-math-redirection-debug-info {
			font-size: 13px;
			font-style: italic;
			font-family: Consolas, Monaco, monospace;
			color: #000;
		}
	</style>
</head>
<body>
	<div class="rank-math-redrection-debug">
		<h1><?php esc_html_e( 'Redirection Debugger', 'rank-math' ); ?></h1>
		<p>
			<?php esc_html_e( 'Redirecting from ', 'rank-math' ); ?><code>/<?php echo $this->uri; ?></code>
			<?php esc_html_e( ' To ', 'rank-math' ); ?><code><a href="<?php echo $this->redirect_to; ?>"><?php echo $this->redirect_to; ?></a></code>
		</p>

		<div class="rank-math-redirection-loading-animation">
			<img src="<?php echo esc_url( $assets_uri . '/assets/loader.svg' ); ?>">
		</div>
		<div class="rank-math-redirection-loading-timer">
			<?php /* translators: countdown seconds */ ?>
			<span class="redirection-timer"><?php printf( esc_html__( 'Redirecting in %s seconds...', 'rank-math' ), '<span id="redirection-timer-counter">5</span>' ); ?></span><br />
			<a href="#" class="button button-secondary" id="rank-math-cancel-redirection"><?php esc_html_e( 'Stop Redirection', 'rank-math' ); ?></a>
		</div>
		<div class="rank-math-redirect-debug-continue" style="display: none;">
			<a href="<?php echo $this->redirect_to; ?>"><?php esc_html_e( 'Continue redirecting', 'rank-math' ); ?></a>
		</div>

		<p>
			<?php $page_url = Helper::get_admin_url( 'redirections' ); ?>
			<?php if ( isset( $this->matched['id'] ) ) : ?>
			<a target="_blank" href="<?php echo $page_url . '&action=edit&redirection=' . $this->matched['id']; ?>"><?php esc_html_e( 'Manage This Redirection', 'rank-math' ); ?></a> or
			<?php endif; ?>
			<a target="_blank" href="<?php echo $page_url; ?>"><?php esc_html_e( 'Manage All Redirections', 'rank-math' ); ?></a>
		</p>

		<p class="rank-math-redirection-debug-info">
			<?php echo wp_kses_post( __( '<strong>Note:</strong> This interstitial page is displayed only to administrators. Site visitors are redirected without delay.', 'rank-math' ) ); ?>
		</p>

		<?php if ( $this->cache ) : ?>
			<code>Served from cache</code>
		<?php endif; ?>
	</div>

	<?php wp_enqueue_script( 'jquery' ); ?>
	<?php do_action( 'admin_footer' ); ?>
	<?php do_action( 'admin_print_footer_scripts' ); ?>

	<script type="text/javascript">
		jQuery(document).ready(function( $ ) {

			var redirectTimer = 5,
				stopRedirection = false;

			var updateTimer = function() {
				if ( stopRedirection ) {
					return;
				}

				redirectTimer--;
				if ( redirectTimer > 1) {
					$( '#redirection-timer-counter' ).text( redirectTimer );
					setTimeout( updateTimer, 1000 );
				} else {
					$( '#redirection-timer-counter' ).text( redirectTimer );
					window.location.href = '<?php echo $this->redirect_to; ?>';
				}
			}

			var cancelRedirection = function() {
				stopRedirection = true;
				$('.rank-math-redirection-loading-animation, .rank-math-redirection-loading-timer, .rank-math-redirection-debug-cancel').hide();
				$('.rank-math-redirect-debug-continue').show();
			}
			setTimeout( updateTimer, 1000 );

			$( '#rank-math-cancel-redirection' ).click( function( event ) {
				event.preventDefault();
				cancelRedirection();
			});

			$( document ).keyup(function( event ) {
				if ( 27 === event.keyCode ) {
					cancelRedirection();
				}
			});
		});
	</script>
</body>
</html>
