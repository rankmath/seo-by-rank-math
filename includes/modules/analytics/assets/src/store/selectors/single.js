/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import { filtersToUrlParams } from '../../functions'

/**
 * Get posts rows.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts rows.
 */
export function getSinglePosts( state ) {
	return state.appData.singlePost
}

/**
 * Get single post data.
 *
 * @param {Object} state The app state.
 * @param {number} id Single post id.
 * @param {string} filters The filter parameter.
 *
 * @return {Object} Return single post.
 */
export function getSinglePost( state, id, filters ) {
	let params = filtersToUrlParams( filters, false )
	params = '' === params ? 'all' : params

	return isUndefined( state.appData.singlePost[ id ] ) ? {} : state.appData.singlePost[ id ][ params ]
}
