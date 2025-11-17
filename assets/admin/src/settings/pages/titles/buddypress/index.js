/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal dependencies
 */
import fields from './fields'

/**
 * Add post type tabs in the Title Settings panel.
 */
const postTypeSettings = () => {
	const postTypePages = {
		buddypress: {
			// Post type label seprator.
			name: 'buddypress',
			title: __( 'BuddyPress:', 'rank-math' ),
			className: 'separator',
			disabled: true,
		},
		'buddypress-groups': {
			fields,
			name: 'buddypress-groups',
			title: (
				<>
					<i className="rm-icon rm-icon-users"></i>
					{ __( 'Groups', 'rank-math' ) }
				</>
			),
			header: {
				title: __( 'Groups', 'rank-math' ),
				description: __( 'This tab contains SEO options for BuddyPress Group pages.', 'rank-math' ),
			},
		},
	}

	return postTypePages
}

export default postTypeSettings()
