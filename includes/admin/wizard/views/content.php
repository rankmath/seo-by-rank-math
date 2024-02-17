<?php
/**
 * Setup wizard content template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin\Wizard
 */

use RankMath\KB;
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

?>
<div class="header">
	<div class="logo text-center">
		<a href="<?php KB::the( 'logo', 'SW Logo' ); ?>" target="_blank"><img src="<?php echo esc_url( rank_math()->plugin_url() . 'assets/admin/img/logo.svg' ); ?>" width="245"></a>
	</div>

	<?php require_once $this->get_view( 'navigation' ); ?>
</div>

<div class="wrapper">

	<div class="main-content wizard-content--<?php echo esc_attr( $this->step_slug ); ?>">

		<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">
			<input type="hidden" name="action" value="<?php echo 'rank-math-registration' === $this->slug ? 'rank_math_save_registration' : 'rank_math_save_wizard'; ?>">
			<input type="hidden" name="step" value="<?php echo esc_attr( $this->step ); ?>">
			<?php wp_nonce_field( 'rank-math-wizard', 'security' ); ?>

			<?php $this->body(); ?>

		</form>

	</div>

</div>

<?php
if ( ! in_array( $this->step_slug, [ 'register', 'ready' ], true ) ) :
	echo sprintf( '<div class="return-to-dashboard"><a href="%s">%s</a></div>', esc_url( 'rank-math-registration' === Param::get( 'page' ) ? admin_url( '/' ) : RankMath\Helper::get_dashboard_url() ), esc_html__( 'Return to dashboard', 'rank-math' ) );
endif;
?>
