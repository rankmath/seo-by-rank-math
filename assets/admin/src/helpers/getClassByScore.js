/**
 * Get class by score.
 *
 * @param {number} score Score.
 *
 * @return {string} Class.
 */
export default function( score ) {
	if ( 80 < score ) {
		return 'good-fk'
	}

	if ( 50 < score ) {
		return 'ok-fk'
	}

	return 'bad-fk'
}
