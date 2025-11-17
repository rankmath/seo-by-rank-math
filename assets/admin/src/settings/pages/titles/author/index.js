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
	name: 'author',
	header: {
		title: __( 'Authors', 'rank-math' ),
		description: __(
			'Change SEO options related to the author archives.',
			'rank-math'
		),
		link: getLink( 'author-settings', 'Options Panel Meta Author Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-users" />
			{ __( 'Authors', 'rank-math' ) }
		</>
	),
	fields,
}
