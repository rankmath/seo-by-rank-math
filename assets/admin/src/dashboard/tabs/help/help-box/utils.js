/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import getLink from '@helpers/getLink'

const { isPro } = rankMath

export const helpItems = [
	{
		heading: __( 'Next steps…', 'rank-math' ),
		items: [
			{
				id: isPro ? 'how-to-setup' : 'upgrade-to-pro',
				icon: isPro ? 'settings' : 'star-filled',
				title: isPro
					? __( 'Setup Rank Math', 'rank-math' )
					: __( 'Upgrade to PRO', 'rank-math' ),
				description: isPro
					? __( 'How to Properly Setup Rank Math', 'rank-math' )
					: __( 'Advanced Schema, Analytics and much more…', 'rank-math' ),
			},
			{
				id: 'seo-import',
				icon: 'import',
				title: __( 'Import Data', 'rank-math' ),
				description: __(
					'How to Import Data from Your Previous SEO Plugin',
					'rank-math'
				),
			},
			{
				id: 'score-100',
				icon: 'post',
				title: __( 'Improve SEO Score', 'rank-math' ),
				description: __(
					'How to Make Your Posts Pass All the Tests',
					'rank-math'
				),
			},
		],
	},
	{
		heading: __( 'Product Support', 'rank-math' ),
		items: [
			{
				id: 'kb-seo-suite',
				icon: 'help',
				title: __( 'Online Documentation', 'rank-math' ),
				description: __(
					'Understand all the capabilities of Rank Math',
					'rank-math'
				),
			},
			{
				id: 'support',
				icon: 'support',
				title: __( 'Ticket Support', 'rank-math' ),
				description: __(
					'Direct help from our qualified support team',
					'rank-math'
				),
			},
			{
				id: 'help-affiliate',
				icon: 'sitemap',
				title: __( 'Affiliate Program', 'rank-math' ),
				description: __( 'Earn flat 30% on every sale!', 'rank-math' ),
			},
		],
	},
]

export const links = {
	'how-to-setup': getLink( 'how-to-setup', 'Help Tab Setup KB' ),
	'upgrade-to-pro': getLink( 'pro', 'Help Tab PRO Link' ),
	'seo-import': getLink( 'seo-import', 'Help Tab Import Data' ),
	'score-100': getLink( 'score-100', 'Help Tab Score KB' ),
	'kb-seo-suite': getLink( 'kb-seo-suite', 'Help Tab KB Link' ),
	support: getLink( 'support', 'Help Tab Ticket' ),
	'help-affiliate': getLink( 'help-affiliate', 'Help Tab Aff Link' ),
}
