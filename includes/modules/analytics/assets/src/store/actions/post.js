/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update posts overview.
 *
 * @param {Array} posts The new posts.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsOverview( posts ) {
	return updateAppData( 'postsOverview', posts )
}

/**
 * Update post summary.
 *
 * @param {Array} summary   The summary.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsSummary( summary ) {
	return updateAppData( 'postsSummary', summary )
}

/**
 * Update analytics summary.
 *
 * @param {Array}  summary   The summary.
 * @param {string} postType Selected postType.
 *
 * @return {Object} An action for redux.
 */
export function updateAnalyticsSummary( summary, postType = '' ) {
	const data = { ...select( 'rank-math' ).getAnalyticsSummary() }

	data[ postType ] = summary
	return updateAppData( 'analyticsSummary', data )
}

/**
 * Update post rows by objects.
 *
 * @param {number} page Page number.
 * @param {Array}  rows The rows.
 * @param {string} params The filter parameter.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsRowsByObjects( page, rows, params ) {
	const data = { ...select( 'rank-math' ).getPostsRowsByObjectsAll() }

	data[ page ] = ! isUndefined( data[ page ] ) ? data[ page ] : {}
	data[ page ][ params ] = rows
	return updateAppData( 'postsRowsByObjects', data )
}
