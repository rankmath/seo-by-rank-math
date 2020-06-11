<?php
/**
 * Search console overview screen.
 *
 * @package Rank_Math
 */

use RankMath\Helper;

$overview = Helper::search_console()->overview;
if ( 0 === $overview->data->total ) : ?>
<br>
<div class="rank-math-notice notice notice-info inline">
	<p>
		<?php /* translators: admin screen link */ ?>
		<?php printf( wp_kses_post( __( 'The data sets are empty in your cache. You can wait for the next cronjob or <strong>Update Manually</strong>. Please navigate to <a href="%s">SEO > Settings > Search Console</a>.', 'rank-math' ) ), esc_url( Helper::get_admin_url( 'options-general#setting-panel-search-console' ) ) ); ?>
	</p>
</div>
	<?php
	return;
endif;
?>
<div class="rank-math-row search-console-analysis-overview">
	<?php
		$overview->display_clicks();
		$overview->display_impressions();
		$overview->display_ctr();
		$overview->display_position();
		$overview->display_keywords();
		$overview->display_pages();
	?>
	<div class="break"></div>
	<div class="column fullwidth">
		<div id="analysis-overview-dashboard">
			<div id="analysis-overview-chart"></div>
			<div id="analysis-overview-filter" style="height: 80px"></div>
		</div>
	</div>
	<div class="break"></div>
	<div class="column halfwidth">
		<h3><?php esc_html_e( 'Click History', 'rank-math' ); ?></h3>
		<div id="analysis-overview-click-history" style="height: 250px"></div>
	</div>
	<div class="column halfwidth">
		<h3><?php esc_html_e( 'Impressions History', 'rank-math' ); ?></h3>
		<div id="analysis-overview-impression-history" style="height: 250px"></div>
	</div>
	<div class="break"></div>
	<div class="column halfwidth">
		<h3><?php esc_html_e( 'CTR History', 'rank-math' ); ?></h3>
		<div id="analysis-overview-ctr-history" style="height: 250px"></div>
	</div>
	<div class="column halfwidth">
		<h3><?php esc_html_e( 'Position History', 'rank-math' ); ?></h3>
		<div id="analysis-overview-position-history" style="height: 250px"></div>
	</div>
</div>
