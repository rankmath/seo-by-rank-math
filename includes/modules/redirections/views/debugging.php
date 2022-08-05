<?php
/**
 * Redirection debugging template.
 *
 * @package    RankMath
 * @subpackage RankMath\Redirections
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

?>
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" <?php language_attributes(); ?>>
<head>
	<title><?php esc_html_e( 'Rank Math SEO Redirection Debugger', 'rank-math' ); ?></title>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<?php $this->do_action( 'redirection/debugger_head' ); ?>
</head>
<body>
	<div class="rank-math-redrection-debug">
		<h1><?php esc_html_e( 'Redirection Debugger', 'rank-math' ); ?></h1>
		<p>
			<?php esc_html_e( 'Redirecting from ', 'rank-math' ); ?><code>/<?php echo esc_html( $this->args['uri'] ); ?></code>
			<?php esc_html_e( ' To ', 'rank-math' ); ?><code><a href="<?php echo esc_url_raw( $this->args['redirect_to'] ); ?>"><?php echo esc_url( $this->args['redirect_to'] ); ?></a></code>
		</p>

		<div class="rank-math-redirection-loading-animation">
			<img src="<?php echo esc_url_raw( rank_math()->plugin_url() . 'assets/admin/img/loader.svg' ); ?>">
		</div>
		<div class="rank-math-redirection-loading-timer">
			<?php /* translators: countdown seconds */ ?>
			<span class="redirection-timer"><?php printf( esc_html__( 'Redirecting in %s seconds...', 'rank-math' ), '<span id="redirection-timer-counter">5</span>' ); ?></span><br />
			<a href="#" class="button button-secondary" id="rank-math-cancel-redirection"><?php esc_html_e( 'Stop Redirection', 'rank-math' ); ?></a>
		</div>
		<div class="rank-math-redirect-debug-continue" style="display: none;">
			<a href="<?php echo esc_url( $this->args['redirect_to'] ); ?>"><?php esc_html_e( 'Continue redirecting', 'rank-math' ); ?></a>
		</div>

		<p>
			<?php $rank_math_redirections_page_url = Helper::get_admin_url( 'redirections' ); ?>
			<?php if ( isset( $this->args['matched']['id'] ) ) : ?>
				<a target="_blank" href="<?php echo esc_url_raw( $rank_math_redirections_page_url . '&action=edit&redirection=' . absint( $this->args['matched']['id'] ) ); ?>"><?php esc_html_e( 'Manage This Redirection', 'rank-math' ); ?></a> 
				<?php esc_html_e( 'or', 'rank-math' ); ?>
			<?php endif; ?>
			<a target="_blank" href="<?php echo esc_url_raw( $rank_math_redirections_page_url ); ?>"><?php esc_html_e( 'Manage All Redirections', 'rank-math' ); ?></a>
		</p>

		<p class="rank-math-redirection-debug-info">
			<?php echo wp_kses_post( __( '<strong>Note:</strong> This interstitial page is displayed only to administrators. Site visitors are redirected without delay.', 'rank-math' ) ); ?>
		</p>

		<?php if ( $this->args['cache'] ) : ?>
			<code><?php esc_html_e( 'Served from cache', 'rank-math' ); ?></code>
		<?php endif; ?>
	</div>

	<?php $this->do_action( 'redirection/debugger_footer' ); ?>
</body>
</html>
