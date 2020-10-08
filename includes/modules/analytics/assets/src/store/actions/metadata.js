/**
 * Update the app data in redux.
 *
 * @param {string}        key       The key for data to update.
 * @param {string|Object} value     The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateAppData( key, value ) {
	return {
		type: 'RANK_MATH_APP_DATA',
		key,
		value,
	}
}

/**
 * Update the app ui data in redux.
 *
 * @param {string}         key   The key for data to update.
 * @param {Object|string}  value The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateAppUi( key, value ) {
	return {
		type: 'RANK_MATH_APP_UI',
		key,
		value,
	}
}
