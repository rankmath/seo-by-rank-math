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
	name: 'authors',
	header: {
		title: __( 'Authors', 'rank-math' ),
		description: __(
			'Set the sitemap options for author archive pages.',
			'rank-math'
		),
		link: getLink( 'configure-sitemaps', 'Options Panel Sitemap Authors Tab', '#authors' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-users" />
			{ __( 'Authors', 'rank-math' ) }
		</>
	),
	fields,
}
