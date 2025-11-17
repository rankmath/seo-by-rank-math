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
	name: 'robots',
	header: {
		title: __( 'Edit robots.txt', 'rank-math' ),
		description: __(
			'Edit your robots.txt file to control what bots see.',
			'rank-math'
		),
		link: getLink( 'edit-robotstxt', 'Options Panel Robots Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-robots" />
			{ __( 'Edit robots.txt', 'rank-math' ) }
		</>
	),
	fields,
}
