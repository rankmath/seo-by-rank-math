<?php
/**
 * SEO Analyzer admin page contents.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

use RankMath\Helper;

defined( 'ABSPATH' ) || exit;
?>
<header class="rank-math-box">
	<h2>
		<span class="title-prefix"><?php esc_html_e( 'SEO Analysis for', 'rank-math' ); ?></span>
		<span><?php echo esc_html( explode( '://', home_url() )[1] ); ?></span>

		<?php if ( Helper::is_site_connected() && ! empty( $analyzer->results ) ) : ?>
			<a href="#" data-what="website" class="rank-math-recheck"><?php esc_html_e( 'Restart SEO Analyzer', 'rank-math' ); ?> <span class="dashicons dashicons-update"></span></a>
			<a href="#analysis-result" class="button button-primary rank-math-view-issues"><?php esc_html_e( 'View Issues', 'rank-math' ); ?></a>
			<?php do_action( 'rank_math/analyzer/results_header' ); ?>
		<?php endif; ?>
	</h2>
</header>

<div class="rank-math-box rank-math-analyzer-result">

	<span class="wp-header-end"></span>

	<?php if ( Helper::is_site_connected() ) : ?>
		<?php include dirname( __FILE__ ) . '/form.php'; ?>

		<?php if ( ! $analyzer->analyse_subpage ) : ?>
			<div class="rank-math-results-wrapper">
				<?php $analyzer->display(); ?>
			</div>
		<?php endif; ?>

	<?php else : ?>
		<div class="rank-math-seo-analysis-header">
			<?php // Translators: placeholders are opening and closing tag for link. ?>
			<h3><?php echo wp_kses_post( sprintf( __( 'Analyze your site by %1$s linking your Rank Math account %2$s', 'rank-math' ), '<a href="' . Helper::get_connect_url() . '" target="_blank">', '</a>' ) ); ?>
		</div>
	<?php endif; ?>
</div>
