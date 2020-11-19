/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch } from '@wordpress/data'

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
 */
export function getAnalyticsSummary() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/analyticsSummary',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateAnalyticsSummary( response )
	} )
}

/**
 * Get posts summary.
 */
export function getPostsSummary() {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsSummary',
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsSummary( response )
	} )
}

/**
 * Get posts rows.
 *
 * @param  {number} page Page number.
 * @param {Object} filters The filters.
 */
export function getPostsRows( page, filters ) {
	let params = ''
	map( filters, ( val, key ) => {
		if ( val ) {
			params += '&' + key + '=1'
		}
	} )

	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsRows?page=' + page + params,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsRows( page, response )
	} )
}

/**
 * Get posts rows by objects.
 *
 * @param {number} page    Page number.
 * @param {Object} filters The filters.
 */
export function getPostsRowsByObjects( page, filters ) {
	let params = ''
	map( filters, ( val, key ) => {
		if ( val ) {
			params += '&' + key + '=1'
		}
	} )

	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/postsRowsByObjects?page=' + page + params,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updatePostsRowsByObjects( page, response )
	} )
}
