/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update keywords.
 *
 * @param {Array} stats The new stats.
 * @param {string} range The day range.
 *
 * @return {Object} An action for redux.
 */
export function updateStats( stats, range ) {
	const app = select( 'rank-math' ).getAppData()
	const data = { ...app.dashboardStats }
	data[ range ] = stats

	return updateAppData( 'dashboardStats', data )
}

/**
 * Update keywords.
 *
 * @param {Array} stats The new stats.
 *
 * @return {Object} An action for redux.
 */
export function updateKeywordsOverview( stats ) {
	return updateAppData( 'keywordsOverview', stats )
}

/**
 * Update keywords.
 *
 * @param {Array} stats The new stats.
 *
 * @return {Object} An action for redux.
 */
export function updateTrackedKeywordsOverview( stats ) {
	return updateAppData( 'trackedKeywordsOverview', stats )
}

/**
 * Update keyword summary.
 *
 * @param {Array} summary The summary.
 *
 * @return {Object} An action for redux.
 */
export function updateKeywordsSummary( summary ) {
	return updateAppData( 'keywordsSummary', summary )
}

/**
 * Update tracked keywords.
 *
 * @param {Array} keywords The keywords.
 *
 * @return {Object} An action for redux.
 */
export function updateTrackedKeywords( keywords ) {
	return updateAppData( 'trackedKeywords', keywords )
}

/**
 * Update keyword rows.
 *
 * @param {number} page Page number.
 * @param {Array} rows The rows.
 *
 * @return {Object} An action for redux.
 */
export function updateKeywordsRows( page, rows ) {
	const data = { ...select( 'rank-math' ).getKeywordsRowsAll() }
	data[ page ] = rows
	return updateAppData( 'keywordsRows', data )
}
