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
	id: 'analytics',
	type: 'component',
	name: 'analytics',
	header: {
		title: __( 'Analytics', 'rank-math' ),
		description: __(
			'See your Google Search Console, Analytics and AdSense data without leaving your WP dashboard.',
			'rank-math'
		),
		link: getLink( 'analytics-settings', 'Options Panel Analytics Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-search-console" />
			{ __( 'Analytics', 'rank-math' ) }
		</>
	),
	fields,
}
