/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

// Dependency Variable
const onHtmlSitemap = {
	html_sitemap: true,
}

export default [
	{
		id: 'html_sitemap',
		type: 'toggle',
		name: __( 'HTML Sitemap', 'rank-math' ),
		desc: __( 'Enable the HTML sitemap.', 'rank-math' ),
		default: false,
	},
	{
		id: 'html_sitemap_display',
		type: 'toggleGroup',
		name: __( 'Display Format', 'rank-math' ),
		desc: __( 'Choose how you want to display the HTML sitemap.', 'rank-math' ),
		options: {
			shortcode: __( 'Shortcode', 'rank-math' ),
			page: __( 'Page', 'rank-math' ),
		},
		default: 'shortcode',
		dep: onHtmlSitemap,
	},
	{
		id: 'html_sitemap_shortcode',
		type: 'text',
		name: __( 'Shortcode', 'rank-math' ),
		desc: __( 'Use this shortcode to display the HTML sitemap.', 'rank-math' ),
		default: '[rank_math_html_sitemap]',
		classes: 'rank-math-code',
		dep: {
			...onHtmlSitemap,
			html_sitemap_display: 'shortcode',
			relation: 'and',
		},
		disabled: true,
	},
	{
		id: 'html_sitemap_page',
		type: 'searchPage',
		name: __( 'Page', 'rank-math' ),
		desc: __( 'Select the page to display the HTML sitemap. Once the settings are saved, the sitemap will be displayed below the content of the selected page.', 'rank-math' ),
		selectedPage: rankMath.htmlSitemapPage,
		dep: {
			...onHtmlSitemap,
			html_sitemap_display: 'page',
			relation: 'and',
		},
	},
	{
		id: 'html_sitemap_sort',
		type: 'select',
		name: __( 'Sort By', 'rank-math' ),
		desc: __( 'Choose how you want to sort the items in the HTML sitemap.', 'rank-math' ),
		options: {
			// Published Date, Modified Date, Alphabetical, Post ID.
			published: __( 'Published Date', 'rank-math' ),
			modified: __( 'Modified Date', 'rank-math' ),
			alphabetical: __( 'Alphabetical', 'rank-math' ),
			post_id: __( 'Post ID', 'rank-math' ),
		},
		default: 'published',
		dep: onHtmlSitemap,
	},
	{
		id: 'html_sitemap_show_dates',
		type: 'toggle',
		name: __( 'Show Dates', 'rank-math' ),
		desc: __( 'Show published dates for each post & page.', 'rank-math' ),
		default: true,
		dep: onHtmlSitemap,
	},
	{
		id: 'html_sitemap_seo_titles',
		type: 'toggleGroup',
		name: __( 'Item Titles', 'rank-math' ),
		desc: __( 'Show the post/term titles, or the SEO titles in the HTML sitemap.', 'rank-math' ),
		options: {
			titles: __( 'Item Titles', 'rank-math' ),
			seo_titles: __( 'SEO Titles', 'rank-math' ),
		},
		default: 'titles',
		dep: onHtmlSitemap,
	},
]
