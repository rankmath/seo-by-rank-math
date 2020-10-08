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
		path: 'rankmath/v1/analytics/dashboard',
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
		path: 'rankmath/v1/analytics/keywordsOverview',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsOverview( response )
	} )
}

/**
 * Get keywords overview.
 */
export function getTrackedKeywordsOverview() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/analytics/trackedKeywordsOverview',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateTrackedKeywordsOverview( response )
	} )
}

/**
 * Get keywords summary.
 */
export function getKeywordsSummary() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/analytics/keywordsSummary',
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
		path: 'rankmath/v1/analytics/keywordsRows?page=' + page,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateKeywordsRows( page, response )
	} )
}

/**
 * Get tracked keywords.
 */
export function getTrackedKeywords() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/analytics/getTrackedKeywords',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateTrackedKeywords( response )
	} )
}
