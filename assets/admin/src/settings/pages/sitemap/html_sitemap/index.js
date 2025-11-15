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
	name: 'html_sitemap',
	header: {
		title: __( 'HTML Sitemap', 'rank-math' ),
		description: __(
			'This tab contains settings related to the HTML sitemap.',
			'rank-math'
		),
		link: getLink( 'sitemap-general', 'Options Panel Sitemap HTML Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-sitemap" />
			{ __( 'HTML Sitemap', 'rank-math' ) }

		</>
	),
	fields,
}
