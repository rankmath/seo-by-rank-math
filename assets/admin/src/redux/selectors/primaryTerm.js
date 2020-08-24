/**
 * Get primary term ID.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return primary term ID.
 */
export function getPrimaryTermID( state ) {
	return state.appData.primaryTerm
}
