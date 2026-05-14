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
	<title><?php esc_html_e( 'Rank Math SEO Redirection Debugger', 'seo-by-rank-math' ); ?></title>
	<meta name="viewport" content="width=device-width"/>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>

	<?php $this->do_action( 'redirection/debugger_head' ); ?>
</head>
<body>
	<div class="rank-math-redrection-debug">
		<h1><?php esc_html_e( 'Redirection Debugger', 'seo-by-rank-math' ); ?></h1>
		<p>
			<?php esc_html_e( 'Redirecting from ', 'seo-by-rank-math' ); ?><code>/<?php echo esc_html( $this->args['uri'] ); ?></code>
			<?php esc_html_e( ' To ', 'seo-by-rank-math' ); ?><code><a href="<?php echo esc_url_raw( $this->args['redirect_to'] ); ?>"><?php echo esc_url( $this->args['redirect_to'] ); ?></a></code>
		</p>

		<div class="rank-math-redirection-loading-animation">
			<img src="<?php echo esc_url_raw( rank_math()->plugin_url() . 'assets/admin/img/loader.svg' ); ?>">
		</div>
		<div class="rank-math-redirection-loading-timer">
			<?php /* translators: countdown seconds */ ?>
			<span class="redirection-timer"><?php printf( esc_html__( 'Redirecting in %s seconds...', 'seo-by-rank-math' ), '<span id="redirection-timer-counter">5</span>' ); ?></span><br />
			<a href="#" class="button button-secondary" id="rank-math-cancel-redirection"><?php esc_html_e( 'Stop Redirection', 'seo-by-rank-math' ); ?></a>
		</div>
		<div class="rank-math-redirect-debug-continue" style="display: none;">
			<a href="<?php echo esc_url( $this->args['redirect_to'] ); ?>"><?php esc_html_e( 'Continue redirecting', 'seo-by-rank-math' ); ?></a>
		</div>

		<p>
			<?php $rank_math_redirections_page_url = Helper::get_admin_url( 'redirections' ); ?>
			<?php if ( isset( $this->args['matched']['id'] ) ) : ?>
				<a target="_blank" href="<?php echo esc_url_raw( $rank_math_redirections_page_url . '&action=edit&redirection=' . absint( $this->args['matched']['id'] ) ); ?>"><?php esc_html_e( 'Manage This Redirection', 'seo-by-rank-math' ); ?></a> 
				<?php esc_html_e( 'or', 'seo-by-rank-math' ); ?>
			<?php endif; ?>
			<a target="_blank" href="<?php echo esc_url_raw( $rank_math_redirections_page_url ); ?>"><?php esc_html_e( 'Manage All Redirections', 'seo-by-rank-math' ); ?></a>
		</p>

		<p class="rank-math-redirection-debug-info">
			<?php echo wp_kses_post( __( '<strong>Note:</strong> This interstitial page is displayed only to administrators. Site visitors are redirected without delay.', 'seo-by-rank-math' ) ); ?>
		</p>

		<?php if ( $this->args['cache'] ) : ?>
			<code><?php esc_html_e( 'Served from cache', 'seo-by-rank-math' ); ?></code>
		<?php endif; ?>
	</div>

	<?php $this->do_action( 'redirection/debugger_footer' ); ?>
</body>
</html>
