/**
 * External dependencies
 */
import { map, includes, compact, findIndex, isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Compatibility from './compatibility'
import Import from './import'
import YourSite from './your-site'
import Analytics from './analytics'
import Sitemap from './sitemap'
import Optimization from './optimization'
import Ready from './ready'
import Role from '../steps/role'
import MonitorRedirection from '../steps/monitor-redirection'
import SchemaMarkup from '../steps/schema-markup'

export default ( activeTab, data ) => {
	const isConfigured = data?.isConfigured
	const isAdvancedMode = isUndefined( data ) || data?.setup_mode === 'advanced'
	const addImport = ! isUndefined( data ) && data?.addImport
	const steps = [
		{
			name: 'compatibility',
			slug: 'requirements',
			title: __( 'Getting Started', 'rank-math' ),
			view: Compatibility,
		},
		{
			name: 'import',
			title: __( 'Import', 'rank-math' ),
			heading: __( 'Import SEO Settings', 'rank-math' ),
			description: __( 'You can import SEO settings from the following plugins:', 'rank-math' ),
			view: Import,
			isDisabled: ! addImport,
		},
		{
			name: 'yoursite',
			title: __( 'Your Site', 'rank-math' ),
			heading: sprintf(
				// translators: sitename
				__( 'Your Website: %s', 'rank-math' ),
				rankMath.blogName
			),
			description: __( 'Let us know a few things about your site…', 'rank-math' ),
			view: YourSite,
		},
		{
			name: 'analytics',
			title: __( 'Analytics', 'rank-math' ),
			heading: __( 'Connect Google&trade; Services', 'rank-math' ),
			description: __( 'Rank Math automates everything, use below button to connect your site with Google Search Console and Google Analytics. It will verify your site and submit sitemaps automatically. ', 'rank-math' ),
			link: getLink( 'help-analytics', 'SW Analytics Step Description' ),
			linkText: __( 'Read more about it here.', 'rank-math' ),
			view: Analytics,
		},
		{
			name: 'sitemaps',
			title: __( 'Sitemaps', 'rank-math' ),
			heading: __( 'Sitemap', 'rank-math' ),
			description: __( 'Choose your Sitemap configuration and select which type of posts or pages you want to include in your Sitemaps.', 'rank-math' ),
			link: getLink( 'configure-sitemaps', 'SW Sitemap Step' ),
			view: Sitemap,
			isDisabled: ! isAdvancedMode,
		},
		{
			name: 'optimization',
			title: __( 'Optimization', 'rank-math' ),
			heading: __( 'SEO Tweaks', 'rank-math' ),
			description: __( 'Automate some of your SEO tasks like making external links nofollow, redirecting attachment pages, etc.', 'rank-math' ),
			link: getLink( 'seo-tweaks', 'SW Optimization Step' ),
			view: Optimization,
			isDisabled: ! isAdvancedMode,
		},
		{
			name: 'ready',
			title: __( 'Ready', 'rank-math' ),
			view: Ready,
		},
		{
			name: 'ready1',
			title: __( 'Advanced Options', 'rank-math' ),
		},
		{
			name: 'role',
			slug: 'rolemanager',
			title: __( 'Role Manager', 'rank-math' ),
			heading: __( 'Role Manager', 'rank-math' ),
			description: __( 'Set capabilities here.', 'rank-math' ),
			view: Role,
		},
		{
			name: 'redirection',
			slug: '404redirection',
			title: __( '404 + Redirection', 'rank-math' ),
			view: MonitorRedirection,
		},
		{
			name: 'schema-markup',
			title: __( 'Schema Markup', 'rank-math' ),
			heading: __( 'Schema Markup', 'rank-math' ),
			description: __( 'Schema adds metadata to your website, resulting in rich search results and more traffic.', 'rank-math' ),
			view: SchemaMarkup,
		},
	]

	const advancedSteps = [ 'ready1', 'role', 'redirection', 'schema-markup' ]
	const activeTabIndex = findIndex( steps, ( step ) => step.name === activeTab )
	const isAdvanced = includes( advancedSteps, activeTab )

	// Loop through the data and add the 'is-done' className to previous tabs
	return compact(
		map( steps, ( step, index ) => {
			if (
				step.isDisabled ||
				( ! isAdvanced && includes( advancedSteps, step.name ) ) ||
				( isAdvanced && ! includes( advancedSteps, step.name ) )
			) {
				return null
			}
			step.disabled = ! isConfigured
			return index < activeTabIndex ? { ...step, className: 'is-done' } : step
		} )
	)
}
