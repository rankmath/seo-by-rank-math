<?php
/**
 * Export panel template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

?>
<form class="rank-math-export-form cmb2-form" action="" method="post">

	<h3><?php esc_html_e( 'Export Settings', 'rank-math' ); ?></h3>

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
