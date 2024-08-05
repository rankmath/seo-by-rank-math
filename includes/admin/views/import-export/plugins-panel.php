<?php
/**
 * Plugins panel template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

use RankMath\Admin\Importers\Detector;

use RankMath\KB;
use RankMath\Helper;

defined( 'ABSPATH' ) || exit;

$detector = new Detector();
$plugins  = $detector->detect();
$first    = empty( $plugins ) ? '' : array_keys( $plugins )[0];
?>
<h2><?php esc_html_e( 'Other Plugins', 'rank-math' ); ?></h2>

<p class="description">
	<?php
	/* translators: Link to learn about import export panel KB article */
	printf( esc_html__( 'If you were using another plugin to add important SEO information to your website before switching to Rank Math SEO, you can import the settings and data here. %s', 'rank-math' ), '<a href="' . esc_url( KB::get( 'import-export-settings', 'Options Panel Import Export Page Other Plugins' ) ) . '" target="_blank">' . esc_html__( 'Learn more about the Import/Export options.', 'rank-math' ) . '</a>' );
	?>
</p>

<form class="rank-math-box no-padding rank-math-export-form cmb2-form" action="" method="post">
	<div class="with-action at-top">

		<?php if ( empty( $plugins ) ) : ?>
			<p class="empty-notice"><?php echo esc_html__( 'No plugin detected with importable data.', 'rank-math' ); ?></p>
		<?php else : ?>
			<div class="rank-math-box-tabs wp-clearfix">
				<?php foreach ( $plugins as $slug => $importer ) : ?>
					<a href="#import-plugin-<?php echo esc_attr( $slug ); ?>" class="<?php echo $slug === $first ? 'active-tab' : ''; ?>">
						<i class="rm-icon rm-icon-import"></i>
						<span class="rank-math-tab-text"><?php echo esc_html( $importer['name'] ); ?></span>
					</a>
				<?php endforeach; ?>
			</div>

			<div class="rank-math-box-content">
				<div class="rank-math-box-inner">
					<?php foreach ( $plugins as $slug => $importer ) : ?>
						<div id="import-plugin-<?php echo esc_attr( $slug ); ?>" class="<?php echo $slug === $first ? ' active-tab' : ''; ?>">
							<table class="form-table cmb2-wrap">
								<tbody>
									<tr class="choices">
										<td colspan="2">
											<ul class="cmb2-checkbox-list cmb2-list no-select-all">
												<?php
												foreach ( $importer['choices'] as $key => $label ) :
													$id = "{$slug}_{$key}";
													?>
													<li>
														<input type="checkbox" class="cmb2-option" name="<?php echo esc_attr( $slug ); ?>[]" id="<?php echo esc_attr( $id ); ?>" value="<?php echo esc_attr( $key ); ?>" checked="checked">
														<label for="<?php echo esc_attr( $id ); ?>"><?php echo wp_kses_post( $label ); ?></label>
													</li>
												<?php endforeach; ?>

												<?php if ( 'redirections' !== $slug ) : ?>
													<li style="margin-top: 20px;">
														<input type="checkbox" class="cmb2-option" name="<?php echo esc_attr( $slug ); ?>[]" id="<?php echo esc_attr( $slug ); ?>_recalculate" value="recalculate" checked="checked">
														<label for="<?php echo esc_attr( $slug ); ?>_recalculate"><?php esc_html_e( 'Calculate SEO Scores', 'rank-math' ); ?></label>
													</li>
												<?php endif; ?>
											</ul>
										</td>
									</tr>
								</tbody>
							</table>

							<footer>
								<button type="button" class="button button-primary rank-math-action" data-action="importPlugin" data-slug="<?php echo esc_attr( $slug ); ?>" data-active="<?php echo esc_attr( is_plugin_active( $importer['file'] ) ); ?>"><?php esc_html_e( 'Import', 'rank-math' ); ?></button>
								<button type="button" class="button button-link-delete rank-math-action" data-action="cleanPlugin" data-slug="<?php echo esc_attr( $slug ); ?>"><?php esc_html_e( 'Clean', 'rank-math' ); ?></button>
							</footer>
						</div>
					<?php endforeach; ?>
				</div>
			</div>
		<?php endif; ?>
	</div>

</form>
