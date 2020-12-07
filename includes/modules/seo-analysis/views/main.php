<?php
/**
 * SEO Analysis admin page contents.
 *
 * @package   RANK_MATH
 * @author    Rank Math <support@rankmath.com>
 * @license   GPL-2.0+
 * @link      https://rankmath.com/wordpress/plugin/seo-suite/
 * @copyright 2019 Rank Math
 */

use RankMath\KB;
use RankMath\Helper;

$assets   = plugin_dir_url( dirname( __FILE__ ) );
$analyzer = Helper::get_module( 'seo-analysis' )->admin->analyzer;

// Header.
rank_math()->admin->display_admin_header();
?>

<div class="wrap rank-math-wrap rank-math-seo-analysis-wrap">

	<div class="container">

		<header class="rank-math-box">
			<h2>
				<?php echo esc_html( get_admin_page_title() ); ?>
				<a class="button button-secondary button-small" href="<?php KB::the( 'seo-analysis' ); ?>" target="_blank"><?php esc_html_e( 'What is this?', 'rank-math' ); ?></a>
			</h2>
			<?php if ( Helper::is_site_connected() && ! empty( $analyzer->results ) ) : ?>
				<div>
					<button data-what="website" class="button button-primary button-xlarge rank-math-recheck"><?php esc_html_e( 'Start Analysis Again', 'rank-math' ); ?></button>
				</div>
			<?php endif; ?>
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
	</div><!--.container-->
</div>
