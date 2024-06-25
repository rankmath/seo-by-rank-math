/**
 * Internal dependencies
 */
import { updateSettingsData } from './settingsData'

/**
 * Update settings.
 *
 * @param {Array} settings The new settings.
 *
 * @return {Object} An action for redux.
 */
export function updateSettings( settings ) {
	return updateSettingsData( 'settings', settings, 'settings' )
}

/**
 * Reset dirty settings to null
 *
 * @return {Object} An action for redux.
 */
export function resetdirtySettings() {
	return updateSettingsData( 'dirtySettings', {} )
}
