<?php
/**
 * Role Manager main view.
 *
 * @package    RankMath
 * @subpackage RankMath\Role_Manager
 */

use RankMath\KB;

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
						/* translators: %s is a Learn More link to the documentation. */
						printf( esc_html__( 'Control which user has access to which options of Rank Math. %s', 'rank-math' ), '<a href="' . esc_url_raw( KB::get( 'role-manager', 'Role Manager Page' ) ) . '" target="_blank">' . esc_html__( 'Learn more', 'rank-math' ) . '</a>.' );
					?>
				</p>
			</header>

			<input type="hidden" name="action" value="rank_math_handle_capabilities">
			<?php
				wp_nonce_field( 'rank-math-handle-capabilities', 'security' );
				$cmb = cmb2_get_metabox( 'rank-math-role-manager', 'rank-math-role-manager' );
				$cmb->show_form();
			?>

			<footer class="form-footer rank-math-ui">
				<input type="submit" name="reset-capabilities" id="rank-math-reset-cmb" value="<?php esc_attr_e( 'Reset', 'rank-math' ); ?>" class="button button-secondary reset-options alignleft">
				<button type="submit" class="button button-primary"><?php esc_html_e( 'Update Capabilities', 'rank-math' ); ?></button>
			</footer>

		</form>

	</div>

</div>

<script>
	jQuery( function( $ ) {
		var toggle = '<p><button class="button button-small toggle-all-capabilities"><?php echo esc_js( __( 'Toggle All', 'rank-math' ) ); ?></button></p>',
			$table = $( '#cmb2-metabox-rank-math-role-manager' );

		$( '.cmb-th', $table ).each( function( index, elem ) {
			$( elem ).append( toggle );
		} );

		$( '.toggle-all-capabilities' ).on( 'click', function( e ) {
			e.preventDefault();

			var $checkboxes = $( this ).closest( '.cmb-row' ).find( 'input.cmb2-option:not(#administrator7)' ),
				should_check = ! $checkboxes.filter(':checked').length;

			$checkboxes.prop( 'checked', should_check );
		} );
	} );
</script>
