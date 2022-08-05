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

<table role="presentation" border="0" cellpadding="0" cellspacing="0" class="stats">
	<tr>
		<td class="col-1">
			<h3><?php esc_html_e( 'Total Impressions', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value'      => $this->get_variable( 'stats_impressions' ),
					'diff'       => $this->get_variable( 'stats_impressions_diff' ),
					'graph'      => true,
					'graph_data' => $this->get_graph_data( 'impressions' ),
				]
			);
			?>
		</td>
		<td class="col-2">
			<h3><?php esc_html_e( 'Total Clicks', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value'      => $this->get_variable( 'stats_clicks' ),
					'diff'       => $this->get_variable( 'stats_clicks_diff' ),
					'graph'      => true,
					'graph_data' => $this->get_graph_data( 'clicks' ),
				]
			);
			?>
		</td>
	</tr>
	<tr>
		<td class="col-1">
			<h3><?php esc_html_e( 'Total Keywords', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value'      => $this->get_variable( 'stats_keywords' ),
					'diff'       => $this->get_variable( 'stats_keywords_diff' ),
					'graph'      => true,
					'graph_data' => $this->get_graph_data( 'keywords' ),
				]
			);
			?>
		</td>
		<td class="col-2">
			<h3><?php esc_html_e( 'Average Position', 'rank-math' ); ?></h3>
			<?php
			$this->template_part(
				'stat',
				[
					'value'          => $this->get_variable( 'stats_position' ),
					'diff'           => $this->get_variable( 'stats_position_diff' ),
					'graph'          => true,
					'graph_data'     => $this->get_graph_data( 'position' ),
					'graph_modifier' => -100,
					'human_number'   => false,
					'invert'         => true,
				]
			);
			?>
		</td>
	</tr>
</table>
