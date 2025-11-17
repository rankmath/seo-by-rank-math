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
	name: 'social',
	header: {
		title: __( 'Social Meta', 'rank-math' ),
		description: __(
			"Add social account information to your website's Schema and Open Graph.",
			'rank-math'
		),
		link: getLink( 'social-meta-settings', 'Options Panel Meta Social Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-social" />
			{ __( 'Social Meta', 'rank-math' ) }
		</>
	),
	fields,
}

