/**
 * WordPress dependencies
 */
import { doAction, applyFilters } from '@wordpress/hooks'

/**
 * Update the app data in redux.
 *
 * @param {string}        key           The key for data to update.
 * @param {string|Object} value         The value to update.
 * @param {string}        settingsKey   The key for data to update.
 * @param {string|Object} settingsValue The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateSettingsData( key, value, settingsKey = false, settingsValue = null ) {
	value = applyFilters( 'rank_math_sanitize_settings', value, key, settingsKey )
	if ( null !== settingsValue ) {
		settingsValue = applyFilters(
			'rank_math_sanitize_settings_value',
			settingsValue,
			key,
			settingsKey
		)
	}

	settingsValue = null === settingsValue ? value : settingsValue

	doAction( 'rank_math_settings_changed', key, value, settingsKey )
	return {
		type: 'RANK_MATH_SETTINGS_DATA',
		key,
		value,
		settingsKey,
		settingsValue,
	}
}

/**
 * Update the app ui data in redux.
 *
 * @param {string}        key   The key for data to update.
 * @param {Object|string} value The value to update.
 *
 * @return {Object} An action for redux.
 */
export function updateAppUi( key, value ) {
	console.log( 'Update app ui...' )
	doAction( 'rank_math_update_app_ui', key, value )
	return {
		type: 'RANK_MATH_APP_UI',
		key,
		value,
	}
}
