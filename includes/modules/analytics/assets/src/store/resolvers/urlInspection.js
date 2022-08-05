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
 * Get posts rows by objects.
 *
 * @param {number} page    Page number.
 * @param {Object} filters The filters parameter.
 * @param {Object} orders  The orders parameter.
 */
export function getIndexingReport( page, filters, orders ) {
	const params = filtersToUrlParams( filters, false ) + filtersToUrlParams( orders, false )
	apiFetch( {
		method: 'GET',
		path: 'rankmath/v1/an/inspectionResults?page=' + page + params,
	} ).then( ( response ) => {
		dispatch( 'rank-math' ).updateIndexingReport( page, response, '' === params ? 'all' : params )
	} )
}

