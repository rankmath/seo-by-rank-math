/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import { filtersToUrlParams } from '../../functions'

/**
 * Get all posts rows by objects.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts rows.
 */
export function getIndexingReportAll( state ) {
	return state.appData.indexingReport
}

/**
 * Get posts rows by objects filtered by page and filter params.
 *
 * @param {Object} state   The app state.
 * @param {number} page    The page number.
 * @param {string} filters The filter parameter.
 * @param {string} orders  The order parameter.
 *
 * @return {string} Return posts rows.
 */
export function getIndexingReport( state, page, filters, orders ) {
	let params = filtersToUrlParams( filters, false ) + filtersToUrlParams( orders, false )
	params = '' === params ? 'all' : params
	return isUndefined( state.appData.indexingReport[ page ] ) ? {} : state.appData.indexingReport[ page ][ params ]
}
