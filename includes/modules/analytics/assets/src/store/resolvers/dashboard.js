/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch } from '@wordpress/data'

/**
 * Get dashboard stats.
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
 * @param  {number} page Page number.
 */
export function getKeywordsRows( page ) {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/keywordsRows?page=' + page,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsRows( page, response )
	} )
}
