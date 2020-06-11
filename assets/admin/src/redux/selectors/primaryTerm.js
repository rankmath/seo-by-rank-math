/**
 * Get primary term id.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return primary term id.
 */
export function getPrimaryTermID( state ) {
	return state.appData.primaryTerm
}
