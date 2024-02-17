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
use RankMath\Helpers\Param;

defined( 'ABSPATH' ) || exit;

$current_tab  = Param::get( 'view', 'seo_analyzer' );
$allowed_tabs = [ 'seo_analyzer', 'competitor_analyzer' ];
if ( ! in_array( $current_tab, $allowed_tabs, true ) ) {
	$current_tab = 'seo_analyzer';
}

$module   = Helper::get_module( 'seo-analysis' );
$analyzer = $module->admin->analyzer;

$tab_file = apply_filters( 'rank_math/seo_analysis/admin_tab_view', '', $current_tab );

// Header.
rank_math()->admin->display_admin_header();
?>

<div class="wrap rank-math-wrap rank-math-seo-analysis-wrap dashboard">
	<span class="wp-header-end"></span>

	<?php $analyzer->admin_tabs(); ?>
	<div class="rank-math-ui dashboard-wrapper seo-analysis <?php echo esc_attr( $current_tab ); ?>">
		<?php
		if ( $tab_file && file_exists( $tab_file ) ) {
			include_once $tab_file;
		} else {
			include_once dirname( __FILE__ ) . '/seo-analyzer.php';
		}
		?>
	</div><!--.rank-math-ui.module-listing.dashboard-wrapper-->
</div>
