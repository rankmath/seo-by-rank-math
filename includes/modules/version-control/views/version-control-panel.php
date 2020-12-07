<?php
/**
 * The Version Control View.
 *
 * @package    RankMath
 * @subpackage RankMath\Version_Control
 */

namespace RankMath;

?>
<form class="rank-math-rollback-form cmb2-form rank-math-box" action="" method="post">

	<header>
		<h3><?php esc_html_e( 'Rollback to Previous Version', 'rank-math' ); ?></h3>
	</header>

	<p><?php esc_html_e( 'If you are facing issues after an update, you can reinstall a previous version with this tool.', 'rank-math' ); ?></p>
	<?php // translators: placeholder is the word "warning". ?>
	<p class="description warning"><strong><?php printf( esc_html__( '%s Previous versions may not be secure or stable. Proceed with caution and always create a backup.', 'rank-math' ), '<span class="rollback-warning">' . esc_html__( 'Warning: ', 'rank-math' ) . '</span>' ); ?></strong></p>

	<table class="form-table">
		<tbody>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Your Version', 'rank-math' ); ?></label></th>
					<td>
						<strong>
							<?php echo esc_html( $current_version ); ?>
						</strong>
						<?php if ( Rollback_Version::is_rollback_version() ) { ?>
							<?php // Translators: placeholder is "Rolled Back Version:". ?>
							<br><?php printf( esc_html__( '%s Auto updates will not work, please update the plugin manually.', 'rank-math' ), '<span class="rollback-version-label">' . esc_html__( 'Rolled Back Version: ', 'rank-math' ) . '</span>' ); ?>
						<?php } ?>
					<?php if ( $current_version === $latest_version ) { ?>
						<p class="description"><?php esc_html_e( 'You are using the latest version of the plugin.', 'rank-math' ); ?></p>
					<?php } else { ?>
						<p class="description"><?php esc_html_e( 'This is the version you are using on this site.', 'rank-math' ); ?></p>
					<?php } ?>
				</td>
			</tr>
			<?php if ( $current_version !== $latest_version ) { ?>
				<tr>
					<th scope="row"><label><?php esc_html_e( 'Latest Stable Version', 'rank-math' ); ?></label></th>
					<td>
						<strong><?php echo esc_html( $latest_version ); ?></strong>
						<?php if ( version_compare( $current_version, $latest_version, '<' ) ) { ?>
							<a href="<?php echo esc_url( self_admin_url( 'update-core.php' ) ); ?>" class="update-link"><?php esc_html_e( 'Update Now', 'rank-math' ); ?></a>
						<?php } ?>
						<p class="description"><?php esc_html_e( 'This is the latest version of the plugin.', 'rank-math' ); ?></p>
					</td>
				</tr>
			<?php } ?>
			<tr>
				<th scope="row"><label><?php esc_html_e( 'Rollback Version', 'rank-math' ); ?></label></th>
				<td>
					<select class="cmb2_select" name="rm_rollback_version" id="rm_rollback_version">
						<?php foreach ( $versions as $version ) { ?>
							<option value="<?php echo esc_attr( $version ); ?>" <?php disabled( ( $version === $current_version ) ); ?>>
								<?php echo esc_html( $version ); ?>
							</option>
						<?php } ?>
					</select>
					<p class="description"><?php esc_html_e( 'Roll back to this version.', 'rank-math' ); ?></p>
				</td>
			</tr>
		</tbody>
	</table>

	<footer>
		<?php wp_nonce_field( 'rank-math-rollback' ); ?>
		<?php // translators: Version number. ?>
		<button type="submit" class="button button-primary button-xlarge" id="rm-rollback-button" data-buttonlabel="<?php esc_attr_e( 'Install Version %s', 'rank-math' ); ?>"><?php esc_html_e( 'Install Selected Version', 'rank-math' ); ?></button>
		<div class="alignright hidden rollback-loading-indicator">
			<span class="loading-indicator-text"><?php esc_html_e( 'Reinstalling, please wait...', 'rank-math' ); ?></span>
			<span class="spinner is-active"></span>
		</div>
	</footer>

</form>
