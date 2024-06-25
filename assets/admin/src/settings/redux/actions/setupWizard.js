/**
 * Internal dependencies
 */
import { updateAppUi } from './settingsData'

/**
 * Set Setup Wizard step.
 *
 * @param {string} step Setup Wizard step.
 * @return {Object} An action for redux.
 */
export function setStep( step ) {
	return updateAppUi( 'currentStep', step )
}
