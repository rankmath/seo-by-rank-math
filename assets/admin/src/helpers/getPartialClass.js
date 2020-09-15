/**
 * External dependencies
 */
import { round } from 'lodash'

/**
 * Get class by score.
 *
 * @param {number} score    Score.
 * @param {number} maxScore Maximum score.
 *
 * @return {string} Class.
 */
export default ( score, maxScore ) => {
	const percentage = round( ( score / maxScore ) * 100 )

	if ( 100 <= percentage ) {
		return
	}

	if ( 49 < percentage ) {
		return 'test-check-good'
	}

	if ( 30 < percentage ) {
		return 'test-check-ok'
	}

	return 'test-check-bad'
}
