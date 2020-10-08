/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update posts.
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
 * @param {Array} summary The summary.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsSummary( summary ) {
	return updateAppData( 'postsSummary', summary )
}

/**
 * Update analytics summary.
 *
 * @param {Array} summary The summary.
 *
 * @return {Object} An action for redux.
 */
export function updateAnalyticsSummary( summary ) {
	return updateAppData( 'analyticsSummary', summary )
}

/**
 * Update post rows.
 *
 * @param {number} page Page number.
 * @param {Array} rows The rows.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsRows( page, rows ) {
	const data = { ...select( 'rank-math' ).getPostsRowsAll() }
	data[ page ] = rows
	return updateAppData( 'postsRows', data )
}

/**
 * Update post rows by objects.
 *
 * @param {number} page Page number.
 * @param {Array}  rows The rows.
 *
 * @return {Object} An action for redux.
 */
export function updatePostsRowsByObjects( page, rows ) {
	const data = { ...select( 'rank-math' ).getPostsRowsByObjectsAll() }
	data[ page ] = rows
	return updateAppData( 'postsRowsByObjects', data )
}

/**
 * Update page speed.
 *
 * @param {Array} data The data.
 *
 * @return {Object} An action for redux.
 */
export function updatePageSpeed( data ) {
	return updateAppData( 'pageSpeed', data )
}
