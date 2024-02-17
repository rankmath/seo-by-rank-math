<?php
/**
 * Locate, retrieve, and display the server's error log.
 *
 * @since      1.0.33
 * @package    RankMath
 * @subpackage RankMath\Status
 * @author     Rank Math <support@rankmath.com>
 */

namespace RankMath\Status;

use RankMath\Helper;
use RankMath\Helpers\Str;

defined( 'ABSPATH' ) || exit;

/**
 * Error_Log class.
 */
class Error_Log {

	/**
	 * Log path.
	 *
	 * @var string|bool
	 */
	private $log_path = null;

	/**
	 * File content.
	 *
	 * @var array
	 */
	private $contents = null;

	/**
	 * Display Database/Tables Details.
	 */
	public function display() {
		?>
		<div class="rank-math-system-status rank-math-box">
			<header>
				<h3><?php esc_html_e( 'Error Log', 'rank-math' ); ?></h3>
			</header>

			<p class="description">
				<?php
				printf(
					// Translators: placeholder is a link to WP_DEBUG documentation.
					esc_html__( 'If you have %s enabled, errors will be stored in a log file. Here you can find the last 100 lines in reversed order so that you or the Rank Math support team can view it easily. The file cannot be edited here.', 'rank-math' ),
					'<a href="https://wordpress.org/support/article/debugging-in-wordpress/" target=_blank" >WP_DEBUG_LOG</a>'
				);
				?>
			</p>

			<?php
			if ( $this->can_load() ) {
				$this->display_copy_button();
				$this->display_textarea();
				$this->display_info();
			}
			?>
		</div>
		<?php
	}

	/**
	 * Show copy button.
	 */
	private function display_copy_button() {
		?>
		<div class="site-health-copy-buttons">
			<div class="copy-button-wrapper">
				<button type="button" class="button copy-button" data-clipboard-text="<?php echo esc_attr( $this->get_error_log_rows( 100 ) ); ?>">
					<?php esc_html_e( 'Copy Log to Clipboard', 'rank-math' ); ?>
				</button>
				<span class="success hidden" aria-hidden="true"><?php esc_html_e( 'Copied!', 'rank-math' ); ?></span>
			</div>
		</div>
		<?php
	}

	/**
	 * Show information about the error log file.
	 */
	private function display_info() {
		?>
		<div class="error-log-info" style="margin-top: 1rem;">
			<code><?php echo esc_html( basename( $this->get_log_path() ) ); ?></code>
			<em>(<?php echo esc_html( Str::human_number( strlen( join( '', $this->contents ) ) ) ); ?>)</em>
		</div>
		<?php
	}

	/**
	 * Show the textarea with the error log.
	 */
	private function display_textarea() {
		?>
		<div id="error-log-wrapper">
			<textarea name="name" rows="16" cols="80" class="code large-text rank-math-code-box" disabled="disabled" id="rank-math-status-error-log"><?php echo esc_textarea( $this->get_error_log_rows( 100 ) ); ?></textarea>
			<script>var textarea = document.getElementById('rank-math-status-error-log'); textarea.scrollTop = textarea.scrollHeight;</script>
		</div>
		<?php
	}

	/**
	 * Get last x rows from the error log.
	 *
	 * @param  integer $limit Max number of rows to return.
	 *
	 * @return string[]       Array of rows of text.
	 */
	private function get_error_log_rows( $limit = -1 ) {
		if ( is_null( $this->contents ) ) {
			$wp_filesystem  = Helper::get_filesystem();
			$this->contents = $wp_filesystem->get_contents_array( $this->get_log_path() );
		}

		if ( -1 === $limit ) {
			return join( '', $this->contents );
		}

		return is_array( $this->contents ) ? join( '', array_slice( $this->contents, -$limit ) ) : '';
	}

	/**
	 * Show error if the log cannot be loaded.
	 */
	private function can_load() {
		$log_file      = $this->get_log_path();
		$wp_filesystem = Helper::get_filesystem();

		if (
			empty( $log_file ) ||
			is_null( $wp_filesystem ) ||
			! Helper::is_filesystem_direct() ||
			! $wp_filesystem->exists( $log_file ) ||
			! $wp_filesystem->is_readable( $log_file )
		) {
			?>
			<strong class="error-log-cannot-display">
				<?php esc_html_e( 'The error log cannot be retrieved.', 'rank-math' ); ?>
			</strong>
			<?php
			return false;
		}

		// Error log must be smaller than 100 MB.
		$size = $wp_filesystem->size( $log_file );
		if ( $size > 100000000 ) {
			?>
			<strong class="error-log-cannot-display">
				<?php esc_html_e( 'The error log cannot be retrieved: Error log file is too large.', 'rank-math' ); ?>
			</strong>
			<?php
			return false;
		}

		return true;
	}

	/**
	 * Get error log file location.
	 *
	 * @return string Path to log file.
	 */
	private function get_log_path() {
		if ( is_null( $this->log_path ) ) {
			$this->log_path = ini_get( 'error_log' );
		}

		return $this->log_path;
	}
}
