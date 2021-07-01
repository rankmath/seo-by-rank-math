/**
 * External dependencies
 */
import { get, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import { filtersToUrlParams } from '../../functions'

/**
 * Get app data.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return dashboard stats.
 */
export function getAppData( state ) {
	return state.appData
}

/**
 * Get dashboard stats.
 *
 * @param {Object} state The app state.
 * @param {string} range The day range.
 *
 * @return {string} Return dashboard stats.
 */
export function getDashboardStats( state, range ) {
	return get( state.appData, [ 'dashboardStats', range ], false )
}

/**
 * Get keywords overview stats.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords overview.
 */
export function getKeywordsOverview( state ) {
	return state.appData.keywordsOverview
}

/**
 * Get keywords summary.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords summary.
 */
export function getKeywordsSummary( state ) {
	return state.appData.keywordsSummary
}

/**
 * Get all keywords rows.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords rows.
 */
export function getKeywordsRowsAll( state ) {
	return state.appData.keywordsRows
}

/**
 * Get keywords rows filtered by page.
 *
 * @param {Object} state   The app state.
 * @param {number} page    The page number.
 * @param {string} filters The filter parameter.
 *
 * @return {string} Return filtered keywords rows.
 */
export function getKeywordsRows( state, page, filters ) {
	let params = filtersToUrlParams( filters, false )
	params = '' === params ? 'all' : params

	return isUndefined( state.appData.keywordsRows[ page ] ) ? {} : state.appData.keywordsRows[ page ][ params ]
}
