/**
 * Get day range.
 *
 * @param {Object} state The app state.
 *
 * @return {string} Return posts rows.
 */
export function getDaysRange( state ) {
	return state.appData.daysRange
}

/**
 * Get userColumnPreference.
 *
 * @param {Object} state The app state.
 * @param {string} key Key to preference.
 *
 * @return {string} Return posts rows.
 */
export function getUserColumnPreference( state, key = false ) {
	return key
		? state.appUi.userColumnPreference[ key ]
		: state.appUi.userColumnPreference
}
