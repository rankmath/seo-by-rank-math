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

use RankMath\KB;

defined( 'ABSPATH' ) || exit;

?>
<div class="rank-math-box blurred">
	<h2>
		Competitor Analyzer
	</h2>
	<p>Enter a site URL to see how it ranks for the same SEO criteria as site.</p>

	<div class="url-form">
		<input type="text" name="competitor_url" id="competitor_url" placeholder="https://rankmath.com" disabled="disabled" />
		<button type="button" class="button button-primary disabled" id="competitor_url_submit" disabled="disabled">Start SEO Analyzer</button>
	</div>

</div>

<div class="rank-math-box rank-math-analyzer-result blurred">
	<span class="wp-header-end"></span>
	<div class="rank-math-results-wrapper">
		<div class="rank-math-result-graphs rank-math-box">
			<div class="three-col">
				<div class="graphs-main">
					<div id="rank-math-circle-progress" data-result="0.85">
						<div class="result-main-score">
							<strong>85/100</strong>
							<label>SEO Score</label>
						</div>
					</div>
				</div>
				<div class="graphs-side">
					<ul class="chart">
						<li class="chart-bar-good">
							<div class="result-score">
								<label>Passed Tests</label>
								<strong>20/29</strong>
							</div>
							<div class="chart-bar">
								<span style="width:69%"></span>
							</div>
						</li>
						<li class="chart-bar-average">
							<div class="result-score">
								<label>Warnings</label>
								<strong>2/29</strong>
							</div>
							<div class="chart-bar">
								<span style="width:7%"></span>
							</div>
						</li>
						<li class="chart-bar-bad">
							<div class="result-score">
								<label>Failed Tests</label>
								<strong>7/29</strong>
							</div>
							<div class="chart-bar">
								<span style="width:24%"></span>
							</div>
						</li>
					</ul>
				</div>
				<div class="serp-preview">
					<div class="serp-preview-body">
						<div class="serp-url-wrapper">
							<img src="https://t0.gstatic.com/faviconV2?client=SOCIAL&amp;type=FAVICON&amp;fallback_opts=TYPE,SIZE,URL&amp;url=https%3A%2F%2Fwww.rankmath.com%2F&amp;size=128" width="16" height="16" class="serp-favicon">
							<span class="serp-url">https://www.rankmath.com/</span>
						</div>
						<h5 class="serp-title">Rank Math - Best Free WordPress SEO Tools in 2022</h5>
						<p class="serp-description">Rank Math WordPress SEO plugin will help you rank higher in search engines. DOWNLOAD for FREE this plugin today to optimize WordPress website for higher ra...</p>
					</div>
				</div>
			</div>
		</div>
		<div id="analysis-result" class="rank-math-result-filters">
			<a href="#all" class="rank-math-result-filter rank-math-result-filter-all active" data-filter="all">All<span class="rank-math-result-filter-count">29</span></a>
			<a href="#passed" class="rank-math-result-filter rank-math-result-filter-passed" data-filter="ok">Passed Tests<span class="rank-math-result-filter-count">20</span></a>
			<a href="#warning" class="rank-math-result-filter rank-math-result-filter-warnings" data-filter="warning">Warnings<span class="rank-math-result-filter-count">2</span></a>
			<a href="#failed" class="rank-math-result-filter rank-math-result-filter-failed" data-filter="fail">Failed Tests<span class="rank-math-result-filter-count">7</span></a>
		</div>
		<div class="rank-math-result-tables rank-math-box">
			<div class="rank-math-result-table rank-math-result-category-basic rank-math-result-statuses-fail rank-math-result-statuses-ok rank-math-result-statuses-warning">
				<div class="category-title">
					Basic SEO				
				</div>
				<div class="table-row rank-math-result-status-info" data-status="info">
					<div class="row-title">
						<div class="status-icon status-info dashicons dashicons-info" title="Info"></div>
						<h3>
							Common Keywords
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>A list of keywords that appear frequently in the text of content.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							Here are the most common keywords we found on page: 
							<div class="wp-tag-cloud"><span class="keyword-cloud-item" style="font-size: 22.00px">rank</span> <span class="keyword-cloud-item" style="font-size: 21.29px">math</span> <span class="keyword-cloud-item" style="font-size: 15.65px">wordpress</span> <span class="keyword-cloud-item" style="font-size: 13.18px">best</span> <span class="keyword-cloud-item" style="font-size: 12.82px">search</span> <span class="keyword-cloud-item" style="font-size: 12.82px">plugin</span> <span class="keyword-cloud-item" style="font-size: 12.47px">site</span> <span class="keyword-cloud-item" style="font-size: 12.47px">features</span> <span class="keyword-cloud-item" style="font-size: 12.47px">support</span> <span class="keyword-cloud-item" style="font-size: 12.47px">google</span></div>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="table-row rank-math-result-status-fail" data-status="fail">
					<div class="row-title">
						<div class="status-icon status-fail dashicons dashicons-no" title="Failed"></div>
						<h3>
							SEO Description
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>SEO analysis of page\'s meta-description.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							<a href="#" class="button button-secondary button-small result-action">How to fix</a>
							The description of page has 184 characters. Most search engines will truncate meta description to 160 characters.
							<code class="full-width">Rank Math WordPress SEO plugin will help you rank higher in search engines. DOWNLOAD for FREE this plugin today to optimize WordPress website for higher rankings and more traffic.</code>
							<div class="clear"></div>
							<div class="how-to-fix-wrapper">
								<div class="analysis-test-how-to-fix">
									<p>Write a meta-description for page. Use target keyword(s) (in a natural way) and write with human readers in mind. Summarize the content - describe the topics article discusses.</p>
									<p>The description should stimulate reader interest and get them to click on the article. Think of it as a mini-advert for content.</p>
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="table-row rank-math-result-status-ok" data-status="ok">
					<div class="row-title">
						<div class="status-icon status-ok dashicons dashicons-yes" title="OK"></div>
						<h3>
							H1 Heading
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>SEO Analysis of the H1 Tags on page.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							One H1 tag was found on page.
							<code class="full-width">WordPress SEO Made Easy</code>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="table-row rank-math-result-status-ok" data-status="ok">
					<div class="row-title">
						<div class="status-icon status-ok dashicons dashicons-yes" title="OK"></div>
						<h3>
							H2 Headings
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>SEO analysis of the H2 headings on page.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							One or more H2 tags were found on page.
							<code class="full-width">Recommended By The Best SEOs On The Planet, What is Rank Math?
							, What you can do with Rank Math, Top-performing World Leading Companies Use Rank Math SEO, Take The Guesswork Out Of WordPress SEO, all-in-one solution for all the SEO needs, Leading SEOs are Loving Rank Math!</code>
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="table-row rank-math-result-status-ok" data-status="ok">
					<div class="row-title">
						<div class="status-icon status-ok dashicons dashicons-yes" title="OK"></div>
						<h3>
							Image ALT Attributes
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>SEO analysis of the "alt" attribute for image tags.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							All images on page have alt attributes.
							<div class="clear"></div>
						</div>
					</div>
				</div>
				<div class="table-row rank-math-result-status-ok" data-status="ok">
					<div class="row-title">
						<div class="status-icon status-ok dashicons dashicons-yes" title="OK"></div>
						<h3>
							Keywords in Title &amp; Description
							<span class="rank-math-tooltip"><em class="dashicons-before dashicons-editor-help"></em><span>SEO analysis of the HTML page\'s Title and meta description content.</span></span>
						</h3>
					</div>
					<div class="row-description">
						<div class="row-content">
							One or more keywords were found in the title and description of page.
							<ul class="info-list">
								<li><strong>title: </strong> rank, math, wordpress, best</li>
								<li><strong>description: </strong> rank, math, wordpress, search, plugin, site</li>
							</ul>
							<div class="clear"></div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</div>

<div id="rank-math-pro-cta" class="center">
	<div class="rank-math-cta-box blue-ticks width-50 top-20 less-padding">
		<h3><?php esc_html_e( 'Competitor Analyzer', 'rank-math' ); ?></h3>
		<ul>
			<li><?php esc_html_e( 'Analyze competitor websites to gain an edge', 'rank-math' ); ?></li>
			<li><?php esc_html_e( 'Evaluate strengths and weaknesses', 'rank-math' ); ?></li>
			<li><?php esc_html_e( 'Explore new keywords and opportunities', 'rank-math' ); ?></li>
			<li><?php esc_html_e( 'Make more informed decisions & strategy', 'rank-math' ); ?></li>
		</ul>
		<a href="<?php KB::the( 'pro', 'Competitor Analyzer Tab' ); ?>" target="_blank" rel="noreferrer" class="button button-primary is-green"><?php esc_html_e( 'Upgrade', 'rank-math' ); ?></a>
	</div>
</div>
