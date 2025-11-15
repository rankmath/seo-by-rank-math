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
	name: 'local',
	header: {
		title: __( 'Local SEO', 'rank-math' ),
		description: __(
			'Optimize for local searches and Knowledge Graph using these settings.',
			'rank-math'
		),
		link: getLink( 'local-seo-settings', 'Options Panel Meta Local Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-local-seo" />
			{ __( 'Local SEO', 'rank-math' ) }
		</>
	),
	fields,
}

