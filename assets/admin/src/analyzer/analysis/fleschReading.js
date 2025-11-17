/**
 * External dependencies
 */
import { forEach, inRange } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Analysis from '@root/analyzer/Analysis'
import AnalysisResult from '@root/analyzer/AnalysisResult'

class FleschReading extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Add some content to calculate Flesch Readability score.', 'rank-math' ) )
			.setTooltip( __( 'Try to make shorter sentences, using less difficult words to improve readability.', 'rank-math' ) )
	}

	/**
	 * Executes the assessment and return its result.
	 *
	 * @param {Paper}      paper      The paper to run this assessment on.
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {AnalysisResult} an AnalysisResult with the score and the formatted text.
	 */
	getResult( paper, researcher ) {
		const analysisResult = this.newResult()
		const fleschReading = researcher.getResearch( 'fleschReading' )
		const fleschScore = fleschReading( paper.getText() )
		const calculatedScore = this.calculateScore( fleschScore )

		analysisResult.setScore( calculatedScore.score )
		analysisResult.setText(
			sprintf(
				/* Translators: 1: score points, 2. score summary. */
				__( 'Your Flesch Readability score is %1$s and is regarded as %2$s', 'rank-math' ),
				fleschScore,
				calculatedScore.note
			)
		)

		analysisResult.note = calculatedScore.note

		return analysisResult
	}

	/**
	 * Checks whether paper meet analysis requirements.
	 *
	 * @param {Paper} paper The paper to use for the assessment.
	 *
	 * @return {boolean} True when requirements meet.
	 */
	isApplicable( paper ) {
		return paper.hasText()
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {number} score Ease score.
	 *
	 * @return {Object} The calculated score.
	 */
	calculateScore( score ) {
		const boundaries = this.getBoundaries()
		let current = 101
		let located = false

		forEach( boundaries, ( boundary ) => {
			if ( inRange( score, boundary.boundary, current ) ) {
				located = boundary
			}

			current = boundary.boundary + 1
		} )

		return false !== located ? located : boundaries.veryDifficult
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return this.getBoundaries().veryEasy.score
	}

	getBoundaries() {
		return applyFilters(
			'rankMath_analysis_fleschReading_boundaries',
			{
				veryEasy: {
					boundary: 90,
					score: 6,
					note: 'very easy',
				},
				easy: {
					boundary: 80,
					score: 5,
					note: 'easy',
				},
				fairlyEasy: {
					boundary: 70,
					score: 5,
					note: 'fairly easy',
				},
				okay: {
					boundary: 60,
					score: 4,
					note: 'okay',
				},
				fairlyDifficult: {
					boundary: 50,
					score: 3,
					note: 'fairly difficult',
				},
				difficult: {
					boundary: 30,
					score: 2,
					note: 'difficult',
				},
				veryDifficult: {
					boundary: 0,
					score: 1,
					note: 'very difficult',
				},
			}
		)
	}
}

export default FleschReading
