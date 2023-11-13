/**
 * Get Content AI score.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords data.
 */
export function isAutoCompleterOpen( state ) {
	return state.appUi.isAutoCompleterOpen
}

/**
 * Get Content AI score.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return keywords data.
 */
export function getContentAiAttributes( state ) {
	return state.appUi.contentAiAttributes
}
