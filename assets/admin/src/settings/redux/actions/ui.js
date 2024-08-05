/**
 * Internal dependencies
 */
import { updateAppUi } from './settingsData'

/**
 * Update app init state.
 *
 * @param {boolean} loaded The state.
 *
 * @return {Object} An action for redux.
 */
export function toggleLoaded( loaded ) {
	return updateAppUi( 'isLoaded', loaded )
}

/**
 * Update the page view.
 *
 * @param {Object} view The current page view.
 *
 * @return {Object} An action for redux.
 */
export function updateView( view ) {
	return updateAppUi( 'view', view )
}
