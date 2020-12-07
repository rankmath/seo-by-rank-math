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

use RankMath\Helper;

?>
<div class="rank-math-result-graphs">

	<div class="two-col">

		<div class="graphs-main">
			<div id="rank-math-circle-progress" data-result="<?php echo ( $percent / 100 ); ?>"><strong class="score-<?php echo $grade; ?>"><?php echo $percent; ?></strong></div>
			<div class="result-score">
				<strong><?php echo $percent; ?>/100</strong>
				<label><?php esc_html_e( 'SEO Score', 'rank-math' ); ?></label>
			</div>
		</div>

		<div class="graphs-side">
			<ul class="chart">
				<li class="chart-bar-good">
					<span style="height:<?php echo round( $statuses['ok'] / $max * 100 ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo $statuses['ok'] . '/' . $total; ?></strong>
						<label><?php esc_html_e( 'Passed Tests', 'rank-math' ); ?></label>
					</div>
				</li>
				<li class="chart-bar-average">
					<span style="height:<?php echo round( $statuses['warning'] / $max * 100 ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo $statuses['warning'] . '/' . $total; ?></strong>
						<label><?php esc_html_e( 'Warnings', 'rank-math' ); ?></label>
					</div>
				</li>
				<li class="chart-bar-bad">
					<span style="height:<?php echo round( $statuses['fail'] / $max * 100 ); ?>%"></span>
					<div class="result-score">
						<strong><?php echo $statuses['fail'] . '/' . $total; ?></strong>
						<label><?php esc_html_e( 'Failed Tests', 'rank-math' ); ?></label>
					</div>
				</li>
			</ul>
		</div>

	</div>

</div>
