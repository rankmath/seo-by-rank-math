/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'

/**
 * Internal Dependencies
 */
import choicesPostTypes from '../../../helpers/choicesPostTypes'
import APIKeyLocation from './APIKeyLocation'

export default [
	{
		id: 'bing_post_types',
		type: 'checkboxlist',
		name: __( 'Auto-Submit Post Types', 'rank-math' ),
		desc: __(
			'Submit posts from these post types automatically to the IndexNow API when a post is published, updated, or trashed.',
			'rank-math'
		),
		options: choicesPostTypes,
		toggleAll: true,
		default: [],
	},
	{
		id: 'indexnow_api_key_location',
		type: 'component',
		Component: APIKeyLocation,
	},
]
