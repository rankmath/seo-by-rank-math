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
 * Get page data.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getData( state ) {
	return state.appData.data
}

/**
 * Get analytics data.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getAnalytics( state ) {
	return state.appData.analytics
}

/**
 * Get modules.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getModules( state ) {
	return state.appData.modules
}
