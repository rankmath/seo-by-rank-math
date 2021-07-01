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
 * Get post data.
 *
 * @param {number} id Single post id.
 * @param {Object} filters The filters.
 */
export function getSinglePost( id, filters ) {
	const params = filtersToUrlParams( filters, false )

	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/post/' + id + '?' + params.substring( 1 ),
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateSinglePost( id, response, '' === params ? 'all' : params )
	} )
}
