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
	name: 'history',
	header: {
		title: __( 'History', 'rank-math' ),
		description: __(
			'The last 100 IndexNow API requests.',
			'rank-math'
		),
		link: getLink( 'instant-indexing', 'Indexing Settings' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-htaccess" />
			{ __( 'History', 'rank-math' ) }
		</>
	),
	fields,
}
