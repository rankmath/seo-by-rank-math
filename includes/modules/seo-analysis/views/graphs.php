<?php
/**
 * SEO Analysis graphs.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

defined( 'ABSPATH' ) || exit;

?>
<div class="rank-math-result-graphs">

	<div class="two-col">

		<div class="graphs-main">
			<div id="rank-math-circle-progress" data-result="<?php echo floatval( $percent / 100 ); ?>"><strong class="score-<?php echo absint( $grade ); ?>"><?php echo absint( $percent ); ?></strong></div>
			<div class="result-score">
				<strong><?php echo absint( $percent ); ?>/100</strong>
				<label><?php esc_html_e( 'SEO Score', 'rank-math' ); ?></label>
			</div>
		</div>

		<div class="graphs-side">
			<ul class="chart">
				<li class="chart-bar-good">
					<span style="height:<?php echo absint( round( $statuses['ok'] / $max * 100 ) ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo absint( $statuses['ok'] ) . '/' . absint( $total ); ?></strong>
						<label><?php esc_html_e( 'Passed Tests', 'rank-math' ); ?></label>
					</div>
				</li>
				<li class="chart-bar-average">
					<span style="height:<?php echo absint( round( $statuses['warning'] / $max * 100 ) ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo absint( $statuses['warning'] ) . '/' . absint( $total ); ?></strong>
						<label><?php esc_html_e( 'Warnings', 'rank-math' ); ?></label>
					</div>
				</li>
				<li class="chart-bar-bad">
					<span style="height:<?php echo absint( round( $statuses['fail'] / $max * 100 ) ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo absint( $statuses['fail'] ) . '/' . absint( $total ); ?></strong>
						<label><?php esc_html_e( 'Failed Tests', 'rank-math' ); ?></label>
					</div>
				</li>
			</ul>
		</div>

	</div>

</div>
