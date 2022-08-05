/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * WordPress dependencies
 */
import { select } from '@wordpress/data'

/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

/**
 * Update post rows by objects.
 *
 * @param {number} page Page number.
 * @param {Array}  rows The rows.
 * @param {string} params The filter parameter.
 *
 * @return {Object} An action for redux.
 */
export function updateIndexingReport( page, rows, params ) {
	const data = { ...select( 'rank-math' ).getIndexingReportAll() }
	data[ page ] = ! isUndefined( data[ page ] ) ? data[ page ] : {}
	data[ page ][ params ] = rows
	return updateAppData( 'indexingReport', data )
}
