/**
 * External Dependencies
 */
import { difference } from 'lodash'

export default ( data ) => {
	let tests = rankMath.assessor.researchesTests
	tests = difference( tests, [
		// Unneeded, has no effect on the score.
		'keywordNotUsed',
	] )

	if ( ! data.isProduct ) {
		return tests
	}

	tests = difference( tests, [
		'keywordInSubheadings',
		'linksHasExternals',
		'linksNotAllExternals',
		'linksHasInternal',
		'titleSentiment',
		'titleHasNumber',
		'contentHasTOC',
	] )

	return tests
}
