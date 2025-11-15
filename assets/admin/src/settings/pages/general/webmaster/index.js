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
	name: 'webmaster',
	header: {
		title: __( 'Webmaster Tools', 'rank-math' ),
		description: __(
			'Enter verification codes for third-party webmaster tools.',
			'rank-math'
		),
		link: getLink( 'webmaster-tools', 'Options Panel Webmaster Tools Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-toolbox" />
			{ __( 'Webmaster Tools', 'rank-math' ) }
		</>
	),
	fields,
}
