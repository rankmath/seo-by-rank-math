<?php
/**
 * Plugin activation template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\Helper;
use RankMath\Admin\Admin_Helper;

defined( 'ABSPATH' ) || exit;

$is_registered = Helper::is_site_connected();
$class         = $is_registered ? 'status-green' : 'status-red';
$activate_url  = Admin_Helper::get_activate_url();
?>
<div class="rank-math-ui dashboard-wrapper container help">
	<div class="rank-math-box <?php echo esc_attr( $class ); ?>">

		<header>

			<h3><?php esc_html_e( 'Account', 'rank-math' ); ?></h3>

			<span class="button button-large <?php echo esc_attr( $class ); ?>"><?php echo $is_registered ? '<i class="rm-icon rm-icon-tick"></i>' . esc_html__( 'Connected', 'rank-math' ) : '<i class="rm-icon rm-icon-cross"></i>' . esc_html__( 'Not Connected', 'rank-math' ); ?></span>

		</header>

		<div class="rank-math-box-content rank-math-ui rank-math-validate-field">

			<form method="post" action="">

				<input type="hidden" name="registration-action" value="<?php echo $is_registered ? 'deregister' : 'register'; ?>">
				<?php wp_nonce_field( 'rank_math_register_product' ); ?>

				<?php if ( ! $is_registered ) : ?>
					<?php // translators: variables used to wrap the text in the strong tag. ?>
					<p><?php printf( wp_kses_post( __( 'The plugin is currently not connected with your Rank Math account. Click on the button below to login or register for FREE using your %1$sGoogle account, Facebook account%2$s or %1$syour email account%2$s.', 'rank-math' ) ), '<strong>', '</strong>' ); ?></p>
					<a href="<?php echo esc_url( $activate_url ); ?>" class="button button-primary button-animated" ><?php esc_html_e( 'Connect Now', 'rank-math' ); ?></a>
				<?php else : ?>
					<?php // translators: variables used to wrap the text in the strong tag. ?>
					<p><?php printf( wp_kses_post( __( 'You have successfully activated Rank Math. If you find the plugin useful, %1$s feel free to recommend it to your friends or colleagues %2$s.', 'rank-math' ) ), '<strong>', '</strong>' ); ?><?php Admin_Helper::get_social_share(); ?></p>
					<div class="frm-submit">
						<button type="submit" class="button button-primary button-xlarge" name="button"><?php echo esc_html__( 'Disconnect Account', 'rank-math' ); ?></button>
					</div>
				<?php endif; ?>
			</form>

		</div>

	</div>
