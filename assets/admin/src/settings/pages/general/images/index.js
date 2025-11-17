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
	name: 'images',
	header: {
		title: __( 'Images', 'rank-math' ),
		description: __(
			'SEO options related to featured images and media appearing in your post content.',
			'rank-math'
		),
		link: getLink( 'image-settings', 'Options Panel Images Tab' ),
	},
	title: (
		<>
			<i className="rm-icon rm-icon-images" />
			{ __( 'Images', 'rank-math' ) }
		</>
	),
	fields,
}
