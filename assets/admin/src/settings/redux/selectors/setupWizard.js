/**
 * Get primary term ID.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return primary term ID.
 */
export function getCurrentStep( state ) {
	return state.appUi.currentStep
}
