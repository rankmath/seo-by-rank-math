/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'

const onDateArchives = {
	disable_date_archives: false,
}

export default [
	{
		id: 'disable_date_archives',
		type: 'toggle',
		name: __( 'Disable Date Archives', 'rank-math' ),
		desc: sprintf(
			// Translators: placeholder is an example URL.
			__(
				'Enable or disable the date archives (e.g: %s). If this option is disabled, the date archives will be redirected to the homepage.',
				'rank-math'
			),
			'<code>domain.com/2019/06/</code>'
		),
		default: true,
	},
	{
		id: 'date_archive_title',
		type: 'selectVariable',
		name: __( 'Date Archive Title', 'rank-math' ),
		desc: __( 'Title tag on day/month/year based archives.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		default: '%date% %page% %sep% %sitename%',
		exclude: [ 'seo_title', 'seo_description' ],
		dep: onDateArchives,
	},
	{
		id: 'date_archive_description',
		type: 'selectVariable',
		as: 'textarea',
		name: __( 'Date Archive Description', 'rank-math' ),
		desc: __( 'Date archive description.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-description rank-math-advanced-option',
		exclude: [ 'seo_title', 'seo_description' ],
		dep: onDateArchives,
	},
	{
		id: 'date_archive_robots',
		type: 'checkboxlist',
		name: __( 'Date Robots Meta', 'rank-math' ),
		desc: __( 'Custom values for robots meta tag on date page.', 'rank-math' ),
		options: choicesRobots,
		classes: 'rank-math-advanced-option rank-math-robots-data',
		dep: onDateArchives,
	},
	{
		id: 'date_advanced_robots',
		type: 'component',
		Component: AdvancedRobots,
		name: __( 'Date Advanced Robots', 'rank-math' ),
		classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
		dep: onDateArchives,
		default: {
			'max-snippet': -1,
			'max-video-preview': -1,
			'max-image-preview': 'large',
		},
	},
	{
		id: '404_title',
		type: 'selectVariable',
		name: __( '404 Title', 'rank-math' ),
		desc: __( 'Title tag on 404 Not Found error page.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		default: 'Page Not Found %sep% %sitename%',
		exclude: [ 'seo_title', 'seo_description' ],
	},
	{
		id: 'search_title',
		type: 'selectVariable',
		name: __( 'Search Results Title', 'rank-math' ),
		desc: __( 'Title tag on search results page.', 'rank-math' ),
		classes: 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		default: '%search_query% %page% %sep% %sitename%',
		exclude: [ 'seo_title', 'seo_description' ],
	},
	{
		id: 'noindex_search',
		type: 'toggle',
		name: __( 'Noindex Search Results', 'rank-math' ),
		desc: __(
			'Prevent search results pages from getting indexed by search engines. Search results could be considered to be thin content and prone to duplicate content issues.',
			'rank-math'
		),
		default: true,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'noindex_archive_subpages',
		type: 'toggle',
		name: __( 'Noindex Subpages', 'rank-math' ),
		desc: __(
			'Prevent all paginated pages from getting indexed by search engines.',
			'rank-math'
		),
		default: false,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'noindex_paginated_pages',
		type: 'toggle',
		name: __( 'Noindex Paginated Single Pages', 'rank-math' ),
		desc: __(
			'Prevent paginated pages of single pages and posts to show up in the search results. This also applies for the Blog page.',
			'rank-math'
		),
		default: false,
		classes: 'rank-math-advanced-option',
	},
	{
		id: 'noindex_password_protected',
		type: 'toggle',
		name: __( 'Noindex Password Protected Pages', 'rank-math' ),
		desc: __(
			'Prevent password protected pages & posts from getting indexed by search engines.',
			'rank-math'
		),
		default: false,
		classes: 'rank-math-advanced-option',
	},
]
