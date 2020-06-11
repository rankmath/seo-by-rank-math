/**
 * Internal dependencies
 */
import { updateAppData, updateAppUi } from './metadata'

/**
 * Update redirection data.
 *
 * @param {string} id          Unique id of data.
 * @param {string} value       Updated meta value.
 *
 * @return {Object} An action for redux.
 */
export function updateRedirection( id, value ) {
	return updateAppData( id, value )
}

/**
 * Update redirection data.
 *
 * @param {Object} item Redirection object.
 *
 * @return {Object} An action for redux.
 */
export function updateRedirectionItem( item ) {
	return updateAppUi( 'redirectionItem', item )
}

/**
 * Reset redirerction to null
 *
 * @return {Object} An action for redux.
 */
export function resetRedirection() {
	return updateAppUi( 'redirectionItem', {} )
}

/**
 * Update has redirect.
 *
 * @param {Object} hasRedirect The has redirect.
 *
 * @return {Object} An action for redux.
 */
export function updateHasRedirect( hasRedirect ) {
	return updateAppUi( 'hasRedirect', hasRedirect )
}
