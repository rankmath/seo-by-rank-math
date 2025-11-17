/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import choicesRobots from '../../../helpers/choicesRobots'
import AdvancedRobots from '../../../components/AdvancedRobots'

const onAuthorArchives = {
	disable_author_archives: 'off',
}

export default [
	{
		id: 'disable_author_archives',
		type: 'toggleGroup',
		name: __( 'Author Archives', 'rank-math' ),
		desc: __(
			'Enables or disables Author Archives. If disabled, the Author Archives are redirected to your homepage. To avoid duplicate content issues, noindex author archives if you keep them enabled.',
			'rank-math'
		),
		options: {
			on: __( 'Disabled', 'rank-math' ),
			off: __( 'Enabled', 'rank-math' ),
		},
		default: rankMath.disableAutorArchive,
	},
	{
		id: 'url_author_base',
		type: 'text',
		name: __( 'Author Base', 'rank-math' ),
		desc: __(
			'Change the <code>/author/</code> part in author archive URLs.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: 'author',
		dep: onAuthorArchives,
	},
	{
		id: 'author_custom_robots',
		type: 'toggle',
		name: __( 'Author Robots Meta', 'rank-math' ),
		desc: __(
			'Select custom robots meta for author page, such as <code>nofollow</code>, <code>noarchive</code>, etc. Otherwise the default meta will be used, as set in the Global Meta tab.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: true,
		dep: onAuthorArchives,
	},
	{
		id: 'author_robots',
		type: 'checkboxlist',
		name: __( 'Author Robots Meta', 'rank-math' ),
		desc: __( 'Custom values for robots meta tag on author page.', 'rank-math' ),
		options: choicesRobots,
		classes: 'rank-math-advanced-option rank-math-robots-data',
		dep: {
			...onAuthorArchives,
			author_custom_robots: true,
			relation: 'and',
		},
	},
	{
		id: 'author_advanced_robots',
		type: 'component',
		Component: AdvancedRobots,
		name: __( 'Author Advanced Robots', 'rank-math' ),
		classes: 'rank-math-advanced-option rank-math-advanced-robots-field',
		dep: {
			...onAuthorArchives,
			author_custom_robots: true,
			relation: 'and',
		},
		default: {
			'max-snippet': -1,
			'max-video-preview': -1,
			'max-image-preview': 'large',
		},
	},
	{
		id: 'author_archive_title',
		type: 'selectVariable',
		name: __( 'Author Archive Title', 'rank-math' ),
		desc: __(
			'Title tag on author archives. SEO options for specific authors can be set with the meta box available in the user profiles.',
			'rank-math'
		),
		classes: 'rank-math-supports-variables rank-math-title rank-math-advanced-option',
		exclude: [ 'seo_title', 'seo_description' ],
		default: '%name% %sep% %sitename% %page%',
		dep: onAuthorArchives,
	},
	{
		id: 'author_archive_description',
		type: 'selectVariable',
		as: 'textarea',
		name: __( 'Author Archive Description', 'rank-math' ),
		desc: __(
			'Author archive meta description. SEO options for specific author archives can be set with the meta box in the user profiles.',
			'rank-math'
		),
		classes: 'rank-math-supports-variables rank-math-description rank-math-advanced-option',
		exclude: [ 'seo_title', 'seo_description' ],
		dep: onAuthorArchives,
	},
	{
		id: 'author_slack_enhanced_sharing',
		type: 'toggle',
		name: __( 'Slack Enhanced Sharing', 'rank-math' ),
		desc: __(
			'When the option is enabled and an author archive is shared on Slack, additional information will be shown (name & total number of posts).',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: true,
		dep: onAuthorArchives,
	},
	{
		id: 'author_add_meta_box',
		type: 'toggle',
		name: __( 'Add SEO Controls', 'rank-math' ),
		desc: __(
			'Add SEO Controls for user profile pages. Access to the Meta Box can be fine tuned with code, using a special filter hook.',
			'rank-math'
		),
		classes: 'rank-math-advanced-option',
		default: true,
		dep: onAuthorArchives,
	},
]
