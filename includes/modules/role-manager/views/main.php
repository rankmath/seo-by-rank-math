<?php
/**
 * Help Role page template.
 *
 * @package    RankMath
 * @subpackage RankMath\Role_Manager
 */

defined( 'ABSPATH' ) || exit;

// Header.
rank_math()->admin->display_admin_header();
?>
<div class="wrap rank-math-wrap">
	<div class="rank-math-box container">

		<span class="wp-header-end"></span>

		<form class="cmb-form" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="post">

			<header>
				<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
				<p>
					<?php
						/* translators: %s is a Learn More link to the documentation */
						printf( __( 'Control which user has access to which options of Rank Math. %s', 'rank-math' ), '<a href="' . \RankMath\KB::get( 'role-manager' ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.' );
					?>
				</p>
			</header>

			<input type="hidden" name="action" value="rank_math_save_capabilities">
			<?php
				wp_nonce_field( 'rank-math-save-capabilities', 'security' );
				$cmb = cmb2_get_metabox( 'rank-math-role-manager', 'rank-math-role-manager' );
				$cmb->show_form();
			?>

			<footer class="form-footer rank-math-ui">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Capabilities', 'rank-math' ); ?></button>
			</footer>

		</form>

	</div>

</div>
