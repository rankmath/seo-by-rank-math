<?php
/**
 * SEO Analysis form.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

use RankMath\Helper;
$analyzer = Helper::get_module( 'seo-analysis' )->admin->analyzer;
?>
<div class="rank-math-seo-analysis-header <?php echo empty( $analyzer->results ) || count( $analyzer->results ) < 30 ? '' : ' hidden'; ?>">

	<?php if ( $analyzer->analyse_subpage ) { ?>
		<p class="page-analysis-selected">
			<?php echo sprintf( esc_html__( 'Selected page: %s', 'rank-math' ), '<a href="' . esc_url( $analyzer->analyse_url ) . '" class="rank-math-current-url" target="_blank">' . $analyzer->analyse_url . '</a>' ); // phpcs:ignore ?>
			<input type="text" class="rank-math-analyze-url" value="<?php echo esc_url( $analyzer->analyse_url ); ?>">
			<button class="button button-secondary rank-math-changeurl-ok"><?php esc_html_e( 'OK', 'rank-math' ); ?></button>
			<button class="button button-secondary rank-math-changeurl"><?php esc_html_e( 'Change URL', 'rank-math' ); ?></button>
		</p>
		<button data-what="page" class="button button-primary button-xlarge rank-math-recheck"><?php esc_html_e( 'Start Page Analysis', 'rank-math' ); ?></button>

		<h2><?php esc_html_e( 'Analysing Page&hellip;', 'rank-math' ); ?></h2>

	<?php } else { ?>
		<button data-what="website" class="button button-primary button-xlarge rank-math-recheck"><?php esc_html_e( 'Start Site-Wide Analysis', 'rank-math' ); ?></button>

		<h2><?php esc_html_e( 'Analysing Website&hellip;', 'rank-math' ); ?></h2>

	<?php } ?>

	<div class="progress-bar">
		<div class="progress"></div>
		<label><span>0%</span> <?php esc_html_e( 'Complete', 'rank-math' ); ?></label>
	</div>

</div>
