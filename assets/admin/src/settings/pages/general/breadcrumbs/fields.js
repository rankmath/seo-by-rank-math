/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import choicesSeparator from '../../../helpers/choicesSeparator'

const { hasBreadcrumbSupport, showBlogPage } = rankMath

// Dependency variables
const onBreadcrumbs = hasBreadcrumbSupport ? '' : { breadcrumbs: true }
const onBreadcrumbsAndBreadcrumbsHome = {
	...onBreadcrumbs,
	breadcrumbs_home: true,
	relation: 'and',
}

export default [
	{
		type: 'notice',
		status: 'warning',
		children: (
			<>
				{ __(
					'Use the following code in your theme template files to display breadcrumbs. ',
					'rank-math'
				) }
				<a
					href={ getLink( 'breadcrumbs-install', 'Options Panel Breadcrumbs Tab' ) }
					target="_blank"
					rel="noreferrer"
				>
					{ __( 'Learn More ', 'rank-math' ) }
				</a>
				<br />
				<code>
					{ '<?php if (function_exists("rank_math_the_breadcrumbs")) rank_math_the_breadcrumbs(); ?>' }
				</code>
				{ __( ' OR ', 'rank-math' ) }
				<code>{ '[rank_math_breadcrumb]' }</code>
			</>
		),
	},
	{
		id: 'breadcrumbs',
		type: 'toggle',
		name: __( 'Enable breadcrumbs function', 'rank-math' ),
		desc: __(
			'Turning off breadcrumbs will hide breadcrumbs inserted in template files too.',
			'rank-math'
		),
		default: true,
		...( hasBreadcrumbSupport
			? {
				force_enable: true,
				disabled: true,
				desc: sprintf(
					// Translators: Code to add support for Rank Math Breadcrumbs.
					__(
						'This option cannot be changed since your theme has added the support for Rank Math Breadcrumbs using: %s',
						'rank-math'
					),
					"<br /><code>add_theme_support( 'rank-math-breadcrumbs' );</code>"
				),
			}
			: {}
		),
	},
	{
		id: 'breadcrumbs_separator',
		type: 'toggleGroup',
		name: __( 'Separator Character', 'rank-math' ),
		desc: __(
			'Separator character or string that appears between breadcrumb items.',
			'rank-math'
		),
		options: choicesSeparator,
		dep: onBreadcrumbs,
		addCustom: true,
		default: '-',
	},
	{
		id: 'breadcrumbs_home',
		type: 'toggle',
		name: __( 'Show Homepage Link', 'rank-math' ),
		desc: __( 'Display homepage breadcrumb in trail.', 'rank-math' ),
		dep: onBreadcrumbs,
		default: true,
	},
	{
		id: 'breadcrumbs_home_label',
		type: 'text',
		name: __( 'Homepage label', 'rank-math' ),
		desc: __(
			'Label used for homepage link (first item) in breadcrumbs.',
			'rank-math'
		),
		dep: onBreadcrumbsAndBreadcrumbsHome,
		default: __( 'Home', 'rank-math' ),
	},
	{
		id: 'breadcrumbs_home_link',
		type: 'text',
		name: __( 'Homepage Link', 'rank-math' ),
		desc: __(
			'Link to use for homepage (first item) in breadcrumbs.',
			'rank-math'
		),
		dep: onBreadcrumbsAndBreadcrumbsHome,
		default: rankMath.homeUrl,
	},
	{
		id: 'breadcrumbs_prefix',
		type: 'text',
		name: __( 'Prefix Breadcrumb', 'rank-math' ),
		desc: __( 'Prefix for the breadcrumb path.', 'rank-math' ),
		dep: onBreadcrumbs,
	},
	{
		id: 'breadcrumbs_archive_format',
		type: 'text',
		name: __( 'Archive Format', 'rank-math' ),
		desc: __( 'Format the label used for archive pages.', 'rank-math' ),
		dep: onBreadcrumbs,
		/* translators: placeholder */
		default: __( 'Archives for %s', 'rank-math' ),
	},
	{
		id: 'breadcrumbs_search_format',
		type: 'text',
		name: __( 'Search Results Format', 'rank-math' ),
		desc: __( 'Format the label used for search results pages.', 'rank-math' ),
		dep: onBreadcrumbs,
		/* translators: placeholder */
		default: __( 'Results for %s', 'rank-math' ),
	},
	{
		id: 'breadcrumbs_404_label',
		type: 'text',
		name: __( '404 label', 'rank-math' ),
		desc: __( 'Label used for 404 error item in breadcrumbs.', 'rank-math' ),
		dep: onBreadcrumbs,
		default: __( '404 Error: page not found', 'rank-math' ),
	},
	{
		id: 'breadcrumbs_remove_post_title',
		type: 'toggle',
		name: __( 'Hide Post Title', 'rank-math' ),
		desc: __( 'Hide Post title from Breadcrumb.', 'rank-math' ),
		dep: onBreadcrumbs,
		default: false,
	},
	{
		id: 'breadcrumbs_ancestor_categories',
		type: 'toggle',
		name: __( 'Show Category(s)', 'rank-math' ),
		desc: __(
			'If category is a child category, show all ancestor categories.',
			'rank-math'
		),
		dep: onBreadcrumbs,
		default: false,
	},
	{
		id: 'breadcrumbs_hide_taxonomy_name',
		type: 'toggle',
		name: __( 'Hide Taxonomy Name', 'rank-math' ),
		desc: __( 'Hide Taxonomy Name from Breadcrumb.', 'rank-math' ),
		dep: onBreadcrumbs,
		default: false,
	},
	...( showBlogPage
		? [
			{
				id: 'breadcrumbs_blog_page',
				type: 'toggle',
				name: __( 'Show Blog Page', 'rank-math' ),
				desc: __( 'Show Blog Page in Breadcrumb.', 'rank-math' ),
				dep: onBreadcrumbs,
				default: false,
			},
		]
		: []
	),
]
