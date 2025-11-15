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
	name: 'content-ai',
	header: {
		title: __( 'Content AI', 'rank-math' ),
		description: __(
			'Get sophisticated AI suggestions for related Keywords, Questions & Links to include in the SEO meta & Content Area.',
			'rank-math'
		),
		link: getLink( 'content-ai-settings', 'Options Panel Content AI Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-content-ai" />
			{ __( 'Content AI', 'rank-math' ) }
		</>
	),
	fields,
}
