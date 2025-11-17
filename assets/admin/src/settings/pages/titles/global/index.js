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
	name: 'global',
	header: {
		title: __( 'Global Meta', 'rank-math' ),
		description: __(
			'Change Global meta settings that take effect across your website.',
			'rank-math'
		),
		link: getLink( 'titles-meta', 'Options Panel Meta Global Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-settings" />
			{ __( 'Global Meta', 'rank-math' ) }
		</>
	),
	fields,
}
