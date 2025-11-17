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
	name: 'others',
	header: {
		title: __( 'Others', 'rank-math' ),
		description: __(
			'Change some uncommon but essential settings here.',
			'rank-math'
		),
		link: getLink( 'other-settings', 'Options Panel Others Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-misc" />
			{ __( 'Others', 'rank-math' ) }
		</>
	),
	fields,
}
