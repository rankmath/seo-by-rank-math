<?php
/**
 * Analytics Report summary table template.
 *
 * @package    RankMath
 * @subpackage RankMath\Admin
 */

defined( 'ABSPATH' ) || exit;

?>

<?php if ( $this->get_variable( 'stats_invalid_data' ) ) { ?>
	<?php return; ?>
<?php } ?>

<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="stats-2">
	<tr>
		<td class="col-1">
			<h3><?php esc_html_e( 'Top 3 Positions', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value' => $this->get_variable( 'stats_top_3_positions' ),
					'diff'  => $this->get_variable( 'stats_top_3_positions_diff' ),
				]
			);
			?>
		</td>
		<td class="col-2">
			<h3><?php esc_html_e( '4-10 Positions', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value' => $this->get_variable( 'stats_top_10_positions' ),
					'diff'  => $this->get_variable( 'stats_top_10_positions_diff' ),
				]
			);
			?>
		</td>
		<td class="col-3">
			<h3><?php esc_html_e( '11-50 Positions', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value' => $this->get_variable( 'stats_top_50_positions' ),
					'diff'  => $this->get_variable( 'stats_top_50_positions_diff' ),
				]
			);
			?>
		</td>
	</tr>
</table>
