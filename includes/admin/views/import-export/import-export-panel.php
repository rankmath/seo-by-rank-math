<?php
/**
 * Import-Export Settings panel template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

?>
<h2><?php esc_html_e( 'Plugin Settings', 'rank-math' ); ?></h2>

<p class="description">
	<?php
	/* translators: Link to learn about import export panel KB article */
	printf( esc_html__( 'Import or export your Rank Math settings, This option is useful for replicating Rank Math settings across multiple websites. %s', 'rank-math' ), '<a href="' . \RankMath\KB::get( 'import-export-settings' ) . '" target="_blank">' . esc_html__( 'Learn more about the Import/Export options.', 'rank-math' ) . '</a>' );
	?>
</p>

<div class="rank-math-box no-padding">
	<div class="rank-math-box-tabs wp-clearfix">
		<a href="#rank-math-import-form" class="active">
			<i class="rm-icon rm-icon-import"></i>
			<span class="rank-math-tab-text"><?php esc_html_e( 'Import Settings', 'rank-math' ); ?></span>
		</a>
		<a href="#rank-math-export-form" class="">
			<i class="rm-icon rm-icon-export"></i>
			<span class="rank-math-tab-text"><?php esc_html_e( 'Export Settings', 'rank-math' ); ?></span>
		</a>
	</div>

	<div class="rank-math-box-content">

		<div class="rank-math-box-inner">

			<form id="rank-math-import-form" class="rank-math-export-form cmb2-form active-tab" action="" method="post" enctype="multipart/form-data" accept-charset="<?php echo esc_attr( get_bloginfo( 'charset' ) ); ?>">
				<p><label for="import-me"><strong><?php esc_html_e( 'Settings File', 'rank-math' ); ?></label></strong><p>
				<input type="file" name="import-me" id="import-me" value="">
				<br>
				<span class="validation-message"><?php esc_html_e( 'Please select a file to import.', 'rank-math' ); ?></span>
				<p class="description"><?php esc_html_e( 'Import settings by locating settings file and clicking "Import settings".', 'rank-math' ); ?></p>

				<footer>
					<?php wp_nonce_field( 'rank-math-import-settings' ); ?>
					<input type="hidden" name="object_id" value="import-plz">
					<input type="hidden" name="action" value="wp_handle_upload">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Import', 'rank-math' ); ?></button>
				</footer>
			</form>

			<form class="rank-math-export-form cmb2-form" id="rank-math-export-form" action="" method="post">

				<ul class="cmb2-checkbox-list no-select-all cmb2-list">
					<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status1" value="general" checked="checked"> <label for="status1"><?php esc_html_e( 'General Settings', 'rank-math' ); ?></label></li>
					<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status2" value="titles" checked="checked"> <label for="status2"><?php esc_html_e( 'Titles &amp; Metas', 'rank-math' ); ?></label></li>
					<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status3" value="sitemap" checked="checked"> <label for="status3"><?php esc_html_e( 'Sitemap Settings', 'rank-math' ); ?></label></li>
					<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status4" value="role-manager" checked="checked"> <label for="status4"><?php esc_html_e( 'Role Manager Settings', 'rank-math' ); ?></label></li>
					<li><input type="checkbox" class="cmb2-option" name="panels[]" id="status5" value="redirections" checked="checked"> <label for="status5"><?php esc_html_e( 'Redirections', 'rank-math' ); ?></label></li>
				</ul>
				<p class="description"><?php esc_html_e( 'Choose the panels to export.', 'rank-math' ); ?></p>

				<footer>
					<?php wp_nonce_field( 'rank-math-export-settings' ); ?>
					<input type="hidden" name="object_id" value="export-plz">
					<button type="submit" class="button button-primary"><?php esc_html_e( 'Export', 'rank-math' ); ?></button>
				</footer>

			</form>
		</div>
	</div>
</div>
