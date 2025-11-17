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
	name: 'breadcrumbs',
	header: {
		title: __( 'Breadcrumbs', 'rank-math' ),
		description: __(
			'Here you can set up the breadcrumbs function.',
			'rank-math'
		),
		link: getLink( 'breadcrumbs', 'Options Panel Breadcrumbs Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-direction" />
			{ __( 'Breadcrumbs', 'rank-math' ) }
		</>
	),
	fields,
}
