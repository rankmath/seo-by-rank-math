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
 * Get selected keyword.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} Return selected keyword.
 */
export function getSelectedKeyword( state ) {
	return state.appUi.selectedKeyword
}

/**
 * Is refreshing results.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return is refreshing.
 */
export function isRefreshing( state ) {
	return state.appUi.refreshResults
}
