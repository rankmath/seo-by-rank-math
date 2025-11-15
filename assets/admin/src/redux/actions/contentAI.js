/**
 * Internal dependencies
 */
import { updateAppData } from './metadata'

export function updateAIScore( score ) {
	return updateAppData( 'contentAIScore', score, 'rank_math_contentai_score', score )
}
