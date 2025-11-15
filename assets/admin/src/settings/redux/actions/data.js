/**
 * External Dependencies
 */
import { forEach, includes } from 'lodash'

/**
 * Internal dependencies
 */
import { updateSettingsData } from './settingsData'

/**
 * Update page data.
 *
 * @param {Array} settings The new settings.
 *
 * @return {Object} An action for redux.
 */
export function updateData( settings ) {
	return updateSettingsData( 'data', settings, 'data' )
}

/**
 * Update analytics.
 *
 * @param {Array} settings The new settings.
 *
 * @return {Object} An action for redux.
 */
export function updateAnalytics( settings ) {
	return updateSettingsData( 'analytics', settings, 'analytics' )
}

/**
 * Update modules.
 *
 * @param {string}  id        The module id.
 * @param {boolean} isChecked Whether the module is Toggle on or off.
 *
 * @return {Object} An action for redux.
 */
export function updateModules( id, isChecked ) {
	const modules = wp.data.select( 'rank-math-settings' ).getData()
	modules[ id ].isActive = isChecked

	forEach( modules, ( module, key ) => {
		if ( ! includes( module.dep_modules, id ) ) {
			return
		}

		let isDisabled = false
		forEach( module.dep_modules, ( depModule ) => {
			if ( ! modules[ depModule ].isActive ) {
				isDisabled = true
			}
		} )
		modules[ key ].isDisabled = isDisabled
		modules[ key ].disabled = isDisabled
	} )
	return updateData( modules )
}

/**
 * Reset dirty settings to null
 *
 * @return {Object} An action for redux.
 */
export function resetdirtySettings() {
	return updateSettingsData( 'dirtySettings', {} )
}

/**
 * Action creator for resetting the store
 *
 * @return {Object} An action for redux.
 */
export function resetStore() {
	return { type: 'RESET_STORE' }
}
