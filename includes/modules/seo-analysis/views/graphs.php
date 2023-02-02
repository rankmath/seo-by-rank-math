<?php
/**
 * SEO Analyzer graphs.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

use RankMath\Helper;

$analyzer = Helper::get_module( 'seo-analysis' )->admin->analyzer;

defined( 'ABSPATH' ) || exit;

?>
<div class="rank-math-result-graphs rank-math-box">

	<div class="rank-math-analysis-date">
		<?php echo $analyzer->get_last_checked_date(); // phpcs:ignore ?>
	</div>

	<div class="three-col">

		<div class="graphs-main">
			<div id="rank-math-circle-progress" data-result="<?php echo floatval( $percent / 100 ); ?>">
				<div class="result-main-score">
					<strong><?php echo absint( $percent ); ?>/100</strong>
					<label><?php esc_html_e( 'SEO Score', 'rank-math' ); ?></label>
				</div>
			</div>
		</div>

		<div class="graphs-side">
			<ul class="chart">
				<li class="chart-bar-good">
					<div class="result-score">
						<label><?php esc_html_e( 'Passed Tests', 'rank-math' ); ?></label>
						<strong><?php echo absint( $statuses['ok'] ) . '/' . absint( $total ); ?></strong>
					</div>
					<div class="chart-bar">
						<span style="width:<?php echo absint( round( $statuses['ok'] / $total * 100 ) ); ?>%"></span>
					</div>
				</li>
				<li class="chart-bar-average">
					<div class="result-score">
						<label><?php esc_html_e( 'Warnings', 'rank-math' ); ?></label>
						<strong><?php echo absint( $statuses['warning'] ) . '/' . absint( $total ); ?></strong>
					</div>
					<div class="chart-bar">
						<span style="width:<?php echo absint( round( $statuses['warning'] / $total * 100 ) ); ?>%"></span>
					</div>
				</li>
				<li class="chart-bar-bad">
					<div class="result-score">
						<label><?php esc_html_e( 'Failed Tests', 'rank-math' ); ?></label>
						<strong><?php echo absint( $statuses['fail'] ) . '/' . absint( $total ); ?></strong>
					</div>
					<div class="chart-bar">
						<span style="width:<?php echo absint( round( $statuses['fail'] / $total * 100 ) ); ?>%"></span>
					</div>
				</li>
			</ul>
		</div>

		<?php $this->display_serp_preview(); ?>

	</div>

</div>
