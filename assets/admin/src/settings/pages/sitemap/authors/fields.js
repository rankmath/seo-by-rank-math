/**
 * External dependencies
 */
import { entries, map } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

const { roles, defaultRoles, data } = rankMath

const onSitemapOrHtmlSitemap = {
	authors_sitemap: true,
	authors_html_sitemap: true,
}

const rolesOptions = map( entries( roles ), ( [ id, label ] ) => ( { id, label } ) )

export default [
	{
		id: 'authors_sitemap',
		type: 'toggle',
		name: __( 'Include in Sitemap', 'rank-math' ),
		desc: __( 'Include author archives in the XML sitemap.', 'rank-math' ),
		default: true,
	},
	{
		id: 'authors_html_sitemap',
		type: 'toggle',
		name: __( 'Include in HTML Sitemap', 'rank-math' ),
		desc: __(
			"Include author archives in the HTML sitemap if it's enabled.",
			'rank-math'
		),
		classes: `rank-math-html-sitemap ${ ! data.html_sitemap ? 'hidden' : '' }`,
		default: true,
	},
	{
		id: 'include_authors_without_posts',
		type: 'toggle',
		name: __( 'Include Authors Without Posts', 'rank-math' ),
		desc: __(
			'Enable this option to include authors in the sitemap even if they have not created any posts. This ensures all author archives are listed, regardless of content availability.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: false,
		dep: onSitemapOrHtmlSitemap,
	},
	{
		id: 'exclude_roles',
		type: 'checkboxlist',
		name: __( 'Exclude User Roles', 'rank-math' ),
		desc: __(
			'Selected roles will be excluded from the XML &amp; HTML sitemaps.',
			'rank-math'
		),
		options: rolesOptions,
		default: defaultRoles,
		dep: onSitemapOrHtmlSitemap,
	},
	{
		id: 'exclude_users',
		type: 'text',
		name: __( 'Exclude Users', 'rank-math' ),
		desc: __(
			'Add user IDs, separated by commas, to exclude them from the sitemap.',
			'rank-math'
		),
		dep: onSitemapOrHtmlSitemap,
	},
]
