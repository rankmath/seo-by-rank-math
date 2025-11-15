/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'

/**
 * Update current step data.
 *
 * @param {string} step Current Step.
 * @param {Array}  data Step data.
 */
export function updateStepData( step, data ) {
	return updateAppUi( step, { ...data } )
}

/**
 * Set Setup Wizard step.
 *
 * @param {string} step Setup Wizard step.
 * @return {Object} An action for redux.
 */
export function setStep( step ) {
	return updateAppUi( 'currentStep', step )
}
