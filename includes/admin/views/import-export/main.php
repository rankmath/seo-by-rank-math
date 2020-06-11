<?php
/**
 * Import/Export page template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

?>
<div class="rank-math-import-export">

	<h2><?php esc_html_e( 'Import &amp; Export', 'rank-math' ); ?></h2>

	<p class="description">
		<?php
		/* translators: Link to learn about import export panel KB article */
		printf( esc_html__( 'Import your previous backed up setting. Or, Export your Rank Math settings and meta data for backup or for reuse on (another) blog. %s', 'rank-math' ), '<a href="' . \RankMath\KB::get( 'import-export-settings' ) . '" target="_blank">' . esc_html__( 'Learn more about the Import/Export option.', 'rank-math' ) . '</a>' );
		?>
	</p>

	<div class="two-col">

		<div class="col rank-math-box">
			<?php include_once 'export-panel.php'; ?>
		</div>

		<div class="col rank-math-box">
			<?php include_once 'import-panel.php'; ?>
		</div>

		<div class="col rank-math-box no-borders">
			<?php include_once 'plugins-panel.php'; ?>
		</div>

		<div class="col rank-math-box no-borders">
			<?php include_once 'backup-panel.php'; ?>
		</div>

	</div>
</div>
