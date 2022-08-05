/**
 * Internal dependencies
 */
import { updateAppUi, updateAppData } from './metadata'

/**
 * Refresh Trends keywords.
 *
 * @param {Object} data An action for redux.
 * @return {Object} An action for redux.
 */
export function updateKeywordsData( data ) {
	return updateAppUi( 'keywordsData', data )
}

export function updateAIScore( score ) {
	return updateAppData( 'contentAIScore', score, 'rank_math_contentai_score' )
}
