/**
 * External dependencies
 */
import { get } from 'lodash'

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
 * Get keywords overview stats.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords overview.
 */
export function getTrackedKeywordsOverview( state ) {
	return state.appData.trackedKeywordsOverview
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
 * Get keywords rows.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords rows.
 */
export function getKeywordsRowsAll( state ) {
	return state.appData.keywordsRows
}

/**
 * Get keywords rows.
 *
 * @param {Object} state   The app state.
 * @param {number} page    The page number.
 * @param {Object} filters The filters.
 * @return {string} Return keywords rows.
 */
export function getKeywordsRows( state, page, filters ) {
	return state.appData.keywordsRows[ page ]
}

/**
 * Get tracked keywords.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords rows.
 */
export function getTrackedKeywords( state ) {
	return state.appData.trackedKeywords
}
