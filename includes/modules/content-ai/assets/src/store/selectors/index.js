/**
 * External dependencies
 */
import { sum, round, isEmpty, map, toNumber, pick } from 'lodash'

/**
 * Whether the Content AI Autocompleter is open
 *
 * @param {Object} state The app state.
 *
 * @return {boolean} Return boolean true if the autocompleter is open.
 */
export function isAutoCompleterOpen( state ) {
	return state.appUi.isAutoCompleterOpen
}

/**
 * Get Content AI Attributes.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} Return Content AI Attributes.
 */
export function getContentAiAttributes( state ) {
	return state.appUi.contentAiAttributes
}

/**
 * Get Content AI data.
 *
 * @param {Object} state The app state.
 *
 * @return {Object} Return Content AI data.
 */
export function getData( state ) {
	return state.appUi.data
}

/**
 * Get Content AI score.
 *
 * @param {Object} state The app state.
 *
 * @return {number} Content AI score.
 */
export function getScore( state ) {
	let score = state.appUi.data.score
	if ( ! score || isEmpty( Object.values( score ) ) ) {
		return 0
	}

	score = map( Object.values( score ), toNumber )
	return round( sum( score ) / score.length )
}

/**
 * Get Previous test results generated from the `FixWithAI` component.
 *
 * @param {Object} state The app state.
 *
 * @return {Array} Array containing the previous results.
 */
export function getPreviousResults( state ) {
	return state.appUi.previousResults
}
