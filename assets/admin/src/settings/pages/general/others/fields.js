/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import createLink from '../../../helpers/createLink'
import choicesPostTypes from '../../../helpers/choicesPostTypes'
import RssVarsTable from './RssVarsTable'

// Dependency variable
const frontendScoreDep = { frontend_seo_score: true }

export default [
	{
		id: 'headless_support',
		type: 'toggle',
		name: __( 'Headless CMS Support', 'rank-math' ),
		desc: sprintf(
			// Translators: placeholder is a link to "Read more".
			__(
				'Enable this option to register a REST API endpoint that returns the HTML meta tags for a given URL. %s',
				'rank-math'
			),
			createLink(
				'headless-support',
				'Others Tab KB Link',
				__( 'Read more', 'rank-math' )
			)
		),
		default: false,
	},
	{
		id: 'frontend_seo_score',
		type: 'toggle',
		name: __( 'Show SEO Score to Visitors', 'rank-math' ),
		desc: __(
			'Proudly display the calculated SEO Score as a badge on the front end. It can be disabled for specific posts in the post editor.',
			'rank-math'
		),
		default: false,
	},
	{
		id: 'frontend_seo_score_post_types',
		type: 'checkboxlist',
		name: __( 'SEO Score Post Types', 'rank-math' ),
		options: choicesPostTypes,
		dep: frontendScoreDep,
		toggleAll: true,
		default: [ 'post' ],
	},
	{
		id: 'frontend_seo_score_template',
		type: 'toggleGroup',
		name: __( 'SEO Score Template', 'rank-math' ),
		desc: __(
			'Change the styling for the front end SEO score badge.',
			'rank-math'
		),
		options: {
			circle: __( 'Circle', 'rank-math' ),
			square: __( 'Square', 'rank-math' ),
		},
		dep: frontendScoreDep,
		default: 'circle',
	},
	{
		id: 'frontend_seo_score_position',
		type: 'toggleGroup',
		name: __( 'SEO Score Position', 'rank-math' ),
		desc: sprintf(
			// translators: 1.SEO Score Shortcode 2. SEO Score function
			__(
				'Display the badges automatically, or insert the %1$s shortcode in your posts and the %2$s template tag in your theme template files.',
				'rank-math'
			),
			'<code>[rank_math_seo_score]</code>',
			'<code>&lt;?php&nbsp;rank_math_the_seo_score();&nbsp;?&gt;</code>'
		),
		classes: 'nob',
		options: {
			bottom: __( 'Below Content', 'rank-math' ),
			top: __( 'Above Content', 'rank-math' ),
			both: __( 'Above & Below Content', 'rank-math' ),
			custom: __( 'Custom (use shortcode)', 'rank-math' ),
		},
		dep: frontendScoreDep,
		default: 'top',
	},
	{
		id: 'support_rank_math',
		type: 'toggle',
		name: __( 'Support Us with a Link', 'rank-math' ),
		desc: sprintf(
			// Translators: %s is the word "nofollow" code tag and second one for the filter link
			__(
				'If you are showing the SEO scores on the front end, this option will insert a %1$s backlink to RankMath.com to show your support. You can change the link & the text by using this %2$s.',
				'rank-math'
			),
			'<code>follow</code>',
			createLink(
				'change-seo-score-backlink',
				'Options Panel Support Us',
				__( 'filter', 'rank-math' )
			)
		),
		dep: frontendScoreDep,
		default: true,
	},
	...( rankMath.canAddUsageTracking
		? [
			{
				id: 'usage_tracking',
				type: 'toggle',
				name: __( 'Usage Tracking', 'rank-math' ),
				desc: sprintf(
					// Translators: %s is the KB link to Usage tracking article.
					__(
						'Share anonymous usage data to help us improve Rank Math. No personal info is collected. %s',
						'rank-math'
					),
					createLink(
						'usage-policy',
						'Others Tab KB Link',
						__( 'Learn more about what data is and isn\'t tracked.', 'rank-math' )
					)
				),
				default: false,
			},
		]
		: []
	),
	{
		id: 'rss_before_content',
		type: 'textarea',
		name: __( 'RSS Before Content', 'rank-math' ),
		desc: __( 'Add content before each post in your site feeds.', 'rank-math' ),
	},
	{
		id: 'rss_after_content',
		type: 'textarea',
		name: __( 'RSS After Content', 'rank-math' ),
		desc: __( 'Add content after each post in your site feeds.', 'rank-math' ),
	},
	{
		id: 'rank_math_rss_vars',
		type: 'component',
		Component: RssVarsTable,
	},
]
