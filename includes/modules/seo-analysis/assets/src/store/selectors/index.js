/**
 * Retreives the results of the analyzed URL
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Returns the analyzer results.
 */
export function getResults( state ) {
	return state.appUi.results
}

/**
 * Retreives the URL being analyzed
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Returns the host url.
 */
export function getUrl( state ) {
	return state.appUi.url
}
