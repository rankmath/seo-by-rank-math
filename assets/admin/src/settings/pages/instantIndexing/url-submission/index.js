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
	name: 'url-submission',
	header: {
		title: __( 'Submit URLs', 'rank-math' ),
		description: __(
			'Send URLs directly to the IndexNow API.',
			'rank-math'
		),
		link: getLink( 'instant-indexing', 'Indexing Submit URLs' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-instant-indexing" />
			{ __( 'Submit URLs', 'rank-math' ) }
		</>
	),
	fields,
}
