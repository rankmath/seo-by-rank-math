<?php
/**
 * Backup panel template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

$backups = get_option( 'rank_math_backups', [] );
?>
<button type="button" class="button button-primary alignright rank-math-action" data-action="createBackup"><?php esc_html_e( 'Create Snapshot', 'rank-math' ); ?></button>

<h3><?php esc_html_e( 'Settings Backup', 'rank-math' ); ?></h3>

<p class="description"><?php esc_html_e( 'Take a snapshot of your plugin settings in case you wish to restore them in future. Use it as backup before making substantial changes to Rank Math settings. For taking a backup of the SEO data of your content, use the XML Export option.', 'rank-math' ); ?></p>

<div class="rank-math-settings-backup-form cmb2-form">
	<div class="list-table with-action">
		<table class="form-table">
			<tbody>
				<?php foreach ( $backups as $key => $backup ) : ?>
					<tr>
						<th>
							<?php
							/* translators: Snapshot formatted date */
							printf( esc_html__( 'Backup: %s', 'rank-math' ), date_i18n( 'M jS Y, H:i a', $key ) );
							?>
						</th>
						<td style="width:195px;padding-left:0;">
							<button type="button" class="button button-secondary button-small rank-math-action" data-action="restoreBackup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Restore', 'rank-math' ); ?></button>
							<button type="button" class="button button-link-delete button-small rank-math-action" data-action="deleteBackup" data-key="<?php echo esc_attr( $key ); ?>"><?php esc_html_e( 'Delete', 'rank-math' ); ?></button>
						</td>
					</tr>
				<?php endforeach; ?>
				<?php if ( empty( $backups ) ) : ?>
					<tr class="hidden">
						<th>
						</th>
						<td style="width:195px;padding-left:0;">
							<button type="button" class="button button-primary rank-math-action" data-action="restoreBackup" data-key=""><?php esc_html_e( 'Restore', 'rank-math' ); ?></button>
							<button type="button" class="button button-link-delete rank-math-action" data-action="deleteBackup" data-key=""><?php esc_html_e( 'Delete', 'rank-math' ); ?></button>
						</td>
					</tr>
				<?php endif; ?>
			</tbody>
		</table>
	</div>

	<p id="rank-math-no-backup-message"<?php echo ! empty( $backups ) ? ' class="hidden"' : ''; ?>><?php esc_html_e( 'There is no backup.', 'rank-math' ); ?></p>

</div>