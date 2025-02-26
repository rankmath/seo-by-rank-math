<?php
/**
 * The Redirection debugger.
 *
 * @since      1.0.92
 * @package    RankMath
 * @subpackage RankMath\Redirections
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Redirections;

use RankMath\Traits\Hooker;

defined( 'ABSPATH' ) || exit;

/**
 * Debugger class.
 */
class Debugger {

	use Hooker;

	/**
	 * Redirector variables.
	 *
	 * @var array
	 */
	private $args = [];

	/**
	 * Constructor.
	 *
	 * @param array $args Redirector variables.
	 */
	public function __construct( $args ) {
		$this->args = $args;
		$this->filter( 'user_has_cap', 'filter_user_has_cap' );
		$this->action( 'rank_math/redirection/debugger_head', 'inline_styles' );
		$this->action( 'rank_math/redirection/debugger_footer', 'inline_scripts' );

		$this->debugger_headers();

		include_once __DIR__ . '/views/debugging.php';
		exit;
	}

	/**
	 * Output CSS for the debugger page.
	 */
	public function inline_styles() {
		?>
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
		<?php
	}

	/**
	 * Output JS for the debugger page.
	 */
	public function inline_scripts() {
		?>
		<script>
			var redirectTimer = 5,
				stopRedirection = false;

			var updateTimer = function() {
				if ( stopRedirection ) {
					return;
				}

				redirectTimer--;
				if ( redirectTimer > 1) {
					document.getElementById( 'redirection-timer-counter' ).textContent = redirectTimer;
					setTimeout( updateTimer, 1000 );
				} else {
					document.getElementById( 'redirection-timer-counter' ).textContent = redirectTimer;
					window.location.href = '<?php echo esc_url_raw( $this->args['redirect_to'] ); ?>';
				}
			}

			var cancelRedirection = function() {
				stopRedirection = true;
				document.querySelector('.rank-math-redirection-loading-animation').style.display = 'none';
				document.querySelector('.rank-math-redirection-loading-timer').style.display = 'none';

				document.querySelector('.rank-math-redirect-debug-continue').style.display = 'block';
			}

			setTimeout( updateTimer, 1000 );

			document.getElementById( 'rank-math-cancel-redirection' ).addEventListener( 'click', function( event ) {
				event.preventDefault();
				cancelRedirection();
			} );

			document.addEventListener( 'keyup', function( event ) {
				if ( 27 === event.keyCode ) {
					cancelRedirection();
				}
			} );
		</script>
		<?php
	}

	/**
	 * Send headers for the debugger page.
	 */
	private function debugger_headers() {
		$headers = [];

		$protocol  = wp_get_server_protocol();
		$headers[] = "$protocol 200 OK";
		$headers[] = 'Content-Type: text/html; charset=UTF-8';

		$headers = $this->do_filter( 'redirection/debugger_headers', $headers );

		foreach ( $headers as $header ) {
			header( $header );
		}
	}
}
