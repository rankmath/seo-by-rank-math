/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import fields from './fields'

export default {
	name: 'misc',
	header: {
		title: __( 'Misc Pages', 'rank-math' ),
		description: __(
			'Customize SEO meta settings of pages like search results, 404s, etc.',
			'rank-math'
		),
		link: getLink( 'misc-settings', 'Options Panel Meta Misc Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-misc" />
			{ __( 'Misc Pages', 'rank-math' ) }
		</>
	),
	fields,
}

