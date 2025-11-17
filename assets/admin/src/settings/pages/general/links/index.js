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
	name: 'links',
	header: {
		title: __( 'Links', 'rank-math' ),
		description: __(
			'Change how some of the links open and operate on your website.',
			'rank-math'
		),
		link: getLink( 'link-settings', 'Options Panel Links Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-link" />
			{ __( 'Links', 'rank-math' ) }
		</>
	),
	fields,
}
