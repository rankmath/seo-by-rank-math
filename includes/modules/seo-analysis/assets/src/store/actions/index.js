/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'

/**
 * Update analyzer results.
 *
 * @param {Object} results Analysis results.
 */
export function updateResults( results ) {
	return updateAppUi( 'results', results )
}

/**
 * Update analyzer url.
 *
 * @param {string} url Analysis url.
 */
export function updateUrl( url ) {
	return updateAppUi( 'url', url )
}
