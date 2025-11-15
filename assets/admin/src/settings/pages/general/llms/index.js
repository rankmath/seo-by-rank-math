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
	name: 'llms',
	header: {
		title: __( 'Edit llms.txt', 'rank-math' ),
		description: __(
			'Configure your llms.txt file for custom crawling/indexing rules.',
			'rank-math'
		),
		link: getLink( 'llms', 'Options Panel LLMS Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-bot" />
			{ __( 'Edit llms.txt', 'rank-math' ) }
		</>
	),
	fields,
}
