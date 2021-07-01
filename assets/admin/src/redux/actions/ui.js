/**
 * Internal dependencies
 */
import { updateAppUi } from './metadata'

/**
 * Update app init state.
 *
 * @param {boolean} loaded The state.
 *
 * @return {Object} An action for redux.
 */
export function toggleLoaded( loaded ) {
	return updateAppUi( 'isLoaded', loaded )
}

/**
 * Update selected keyword.
 *
 * @param {Object} keyword The selected keyword.
 *
 * @return {Object} An action for redux.
 */
export function updateSelectedKeyword( keyword ) {
	return updateAppUi( 'selectedKeyword', keyword )
}

/**
 * Refresh results.
 *
 * @return {Object} An action for redux.
 */
export function refreshResults() {
	return updateAppUi( 'refreshResults', Date.now() )
}

/**
 * Set version.
 *
 * @return {Object} An action for redux.
 */
export function setVersion() {
	return updateAppUi( 'isPro', true )
}

/**
 * Update Divi settings bar active within Divi frontend builder.
 *
 * @param {boolean} active The state.
 *
 * @return {Object} An action for redux.
 */
export function toggleIsDiviPageSettingsBarActive( active ) {
	return updateAppUi( 'isDiviPageSettingsBarActive', active )
}

/**
 * Update RankMath sidebar active within Divi frontend builder.
 *
 * @param {boolean} active The state.
 *
 * @return {Object} An action for redux.
 */
export function toggleIsDiviRankMathModalActive( active ) {
	return updateAppUi( 'isDiviRankMathModalActive', active )
}
