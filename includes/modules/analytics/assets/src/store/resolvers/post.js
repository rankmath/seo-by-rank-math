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
 * Get posts overview.
 */
export function getPostsOverview() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsOverview',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsOverview( response )
	} )
}

/**
 * Get analytics summary.
 *
 * @param {string} postType Selected Analytics Post type.
 */
export function getAnalyticsSummary( postType = '' ) {
	const param = postType ? `?postType=${ postType }` : ''
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/analyticsSummary' + param,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateAnalyticsSummary( response, postType )
	} )
}

/**
 * Get posts summary.
 *
 * @param {string} postType Selected Analytics Post type.
 */
export function getPostsSummary( postType ) {
	const param = postType ? `?postType=${ postType }` : ''
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsSummary' + param,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsSummary( response )
	} )
}

/**
 * Get posts rows by objects.
 *
 * @param {number} page     Page number.
 * @param {Object} filters  The filters parameter.
 * @param {Object} orders   The orders parameter.
 * @param {string} postType Selected Analytics Post type.
 */
export function getPostsRowsByObjects( page, filters, orders, postType = '' ) {
	let params = filtersToUrlParams( filters ) + filtersToUrlParams( orders, false )
	params += postType ? `&postType=${ postType }` : ''
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsRowsByObjects?page=' + page + params,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsRowsByObjects( page, response, '' === params ? 'all' : params )
	} )
}
