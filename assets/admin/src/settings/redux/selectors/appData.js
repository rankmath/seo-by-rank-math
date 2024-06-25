/**
 * Get app data from redux.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getAppData( state ) {
	return state.appData
}

/**
 * Get post dirty settings data from redux.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getdirtySettings( state ) {
	return state.appData.dirtySettings
}

/**
 * Get settings.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getSettings( state ) {
	return state.appData.settings
}

/**
 * Get role capabilities.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getRoleCapabilities( state ) {
	return state.appData.roleCapabilities
}
