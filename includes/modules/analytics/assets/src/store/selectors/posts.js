/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import { filtersToUrlParams } from '../../functions'

/**
 * Get posts overview stats.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts overview.
 */
export function getPostsOverview( state ) {
	return state.appData.postsOverview
}

/**
 * Get posts summary.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts summary.
 */
export function getPostsSummary( state ) {
	return state.appData.postsSummary
}

/**
 * Get analytics summary.
 *
 * @param {Object} state    The app state.
 * @param {string} postType Selected post type.
 *
 * @return {string} Return analytics summary.
 */
export function getAnalyticsSummary( state, postType = '' ) {
	const summary = ! isUndefined( state.appData.analyticsSummary[ postType ] ) ? state.appData.analyticsSummary[ postType ] : state.appData.analyticsSummary
	return applyFilters( 'rankMath.analytics.analyticsSummary', summary, state.appData.analyticsSummary )
}

/**
 * Get all posts rows by objects.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts rows.
 */
export function getPostsRowsByObjectsAll( state ) {
	return state.appData.postsRowsByObjects
}

/**
 * Get posts rows by objects filtered by page and filter params.
 *
 * @param {Object} state    The app state.
 * @param {number} page     The page number.
 * @param {string} filters  The filter parameter.
 * @param {string} orders   The order parameter.
 * @param {string} postType Selected Post type.
 *
 * @return {string} Return posts rows.
 */
export function getPostsRowsByObjects( state, page, filters, orders, postType = '' ) {
	let params = filtersToUrlParams( filters ) + filtersToUrlParams( orders, false )
	params = '' === params ? 'all' : params
	params = postType ? params + '&postType=' + postType : params

	return isUndefined( state.appData.postsRowsByObjects[ page ] ) ? {} : state.appData.postsRowsByObjects[ page ][ params ]
}
