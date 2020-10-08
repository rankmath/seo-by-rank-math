/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import apiFetch from '@wordpress/api-fetch'
import { dispatch } from '@wordpress/data'

/**
 * Get posts rows.
 *
 * @param {number} id Single post id.
 */
export function getSinglePost( id ) {
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/analytics/post/' + id,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateSinglePost( id, response )
	} )
}

/**
 * Get page speed.
 *
 * @param {number} id Single post record id.
 * @param {number} post Single post id.
 */
export function getPagespeed( id, post ) {
	if ( isUndefined( post ) ) {
		return
	}

	const { pagespeed_refreshed: refreshed, object_id: objectID } = post

	if ( null === refreshed || '0000-00-00 00:00:00' === refreshed ) {
		apiFetch( {
			method: 'POST',
			path: 'rankmath/v1/analytics/getPagespeed/',
			data: {
				id,
				objectID,
			},
		} ).then( ( response ) => {
			dispatch( 'rank-math' ).updatePagespeed( id, response )
		} )
	}

	dispatch( 'rank-math' ).updatePagespeed( id, post )
}
