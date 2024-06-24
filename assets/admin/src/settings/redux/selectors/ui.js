/**
 * Is app loaded.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return app loaded.
 */
export function isLoaded( state ) {
	return state.appUi.isLoaded
}

/**
 * Get current view.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} Return selected keyword.
 */
export function getView( state ) {
	return state.appUi.view
}
