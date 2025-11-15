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
	name: 'homepage',
	header: {
		title: __( 'Homepage', 'rank-math' ),
		description: __(
			'Add SEO meta and OpenGraph details to your homepage.',
			'rank-math'
		),
		link: getLink( 'homepage-settings', 'Options Panel Meta Home Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-home" />
			{ __( 'Homepage', 'rank-math' ) }
		</>
	),
	fields,
}

