/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { filtersToUrlParams } from '../../functions'

/**
 * Get analytics overview.
 *
 * @param {string} range The day range.
 */
export function getDashboardStats( range ) {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/dashboard',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateStats( response, range )
	} )
}

/**
 * Get keywords overview.
 */
export function getKeywordsOverview() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/keywordsOverview',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsOverview( response )
	} )
}

/**
 * Get keywords summary.
 */
export function getKeywordsSummary() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/keywordsSummary',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsSummary( response )
	} )
}

/**
 * Get keywords rows.
 *
 * @param {number} page Page number.
 * @param {Object} filters The filters.
 */
export function getKeywordsRows( page, filters ) {
	const params = filtersToUrlParams( filters, false )
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/keywordsRows?page=' + page + params,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsRows( page, response, '' === params ? 'all' : params )
	} )
}
