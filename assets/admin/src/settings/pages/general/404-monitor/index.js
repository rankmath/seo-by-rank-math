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
	name: '404-monitor',
	header: {
		title: __( '404 Monitor', 'rank-math' ),
		description: __(
			'Monitor broken pages that ruin user-experience and affect SEO.',
			'rank-math'
		),
		link: getLink( '404-monitor-settings', 'Options Panel 404 Monitor Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-404" />
			{ __( '404 Monitor', 'rank-math' ) }
		</>
	),
	fields,
}
