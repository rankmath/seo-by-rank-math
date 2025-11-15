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
	name: 'redirections',
	header: {
		title: __( 'Redirections', 'rank-math' ),
		description: __(
			'Easily create redirects without fiddling with tedious code.',
			'rank-math'
		),
		link: getLink( 'redirections-settings', 'Options Panel Redirections Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-redirection" />
			{ __( 'Redirections', 'rank-math' ) }
		</>
	),
	fields,
}
