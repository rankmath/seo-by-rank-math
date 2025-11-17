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
	name: 'general',
	header: {
		title: __( 'General', 'rank-math' ),
		description: __(
			'This tab contains General settings related to the XML sitemaps.',
			'rank-math'
		),
		link: getLink( 'configure-sitemaps', 'Options Panel Sitemap General' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-settings" />
			{ __( 'General', 'rank-math' ) }
		</>
	),
	fields,
}
