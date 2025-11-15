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
	name: 'settings',
	header: {
		title: __( 'Settings', 'rank-math' ),
		description: __(
			'Instant Indexing module settings.',
			'rank-math'
		),
		link: getLink( 'instant-indexing', 'Indexing Settings' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-settings" />
			{ __( 'Settings', 'rank-math' ) }
		</>
	),
	fields,
}
