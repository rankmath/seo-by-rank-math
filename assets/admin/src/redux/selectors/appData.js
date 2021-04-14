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
 * Get post dirty metadata from redux.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} The app data.
 */
export function getDirtyMetadata( state ) {
	return state.appData.dirtyMetadata
}

/**
 * Get analysis score.
 *
 * @param {Object} state The app state.
 *
 * @return {number} The analysis score.
 */
export function getAnalysisScore( state ) {
	return state.appData.score
}

/**
 * Get keywords.
 *
 * @param {Object} state The app state.
 *
 * @return {Array} Return focus keywords.
 */
export function getKeywords( state ) {
	return state.appData.keywords
}

/**
 * Get pillarContent.
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return is marked as pillar content.
 */
export function getPillarContent( state ) {
	return state.appData.pillarContent
}

/**
 * Get robots.
 *
 * @param {Object} state The app state.
 *
 * @return {Array} Return post robots array.
 */
export function getRobots( state ) {
	return state.appData.robots
}

/**
 * Get advanced robots.
 *
 * @param {Object} state The app state.
 *
 * @return {Array} Return post advanced robots array.
 */
export function getAdvancedRobots( state ) {
	return state.appData.advancedRobots
}

/**
 * Get canonical Url.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return canonical url.
 */
export function getCanonicalUrl( state ) {
	return state.appData.canonicalUrl
}

/**
 * Get breadcrumb title.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return breadcrumb title.
 */
export function getBreadcrumbTitle( state ) {
	return state.appData.breadcrumbTitle
}

/**
 * Get rich snippet data.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return rich snippet data.
 */
export function getRichSnippets( state ) {
	return 'todo'
}

/**
 * Get show score.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return show score.
 */
export function getShowScoreFrontend( state ) {
	return state.appData.showScoreFrontend
}
