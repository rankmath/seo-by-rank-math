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

class LengthPermalink extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'URL unavailable. Add a short URL.', 'rank-math' ) )
			.setTooltip( __( 'Permalink should be at most 75 characters long.', 'rank-math' ) )
	}

	/**
	 * Executes the assessment and return its result
	 *
	 * @param {Paper}      paper      The paper to run this assessment on.
	 * @param {Researcher} researcher The researcher used for the assessment.
	 *
	 * @return {AnalysisResult} an AnalysisResult with the score and the formatted text.
	 */
	getResult( paper, researcher ) {
		const analysisResult = this.newResult()
		const permalinkLength = paper.getUrl().length

		analysisResult
			.setScore( this.calculateScore( permalinkLength ) )
			.setText(
				sprintf(
					this.translateScore( analysisResult ),
					permalinkLength
				)
			)

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
		return paper.hasUrl()
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {number} permalinkLength Length of Url to run the analysis on.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( permalinkLength ) {
		return 75 < permalinkLength ? null : this.getScore()
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_permalinkLength_score', 4 )
	}

	/**
	 * Translates the score to a message the user can understand.
	 *
	 * @param {AnalysisResult} analysisResult AnalysisResult with the score and the formatted text.
	 *
	 * @return {string} The translated string.
	 */
	translateScore( analysisResult ) {
		return analysisResult.hasScore() ?
			/* Translators: The placeholder is the number of characters. */
			__( 'URL is %1$d characters long. Kudos!', 'rank-math' ) :
			/* Translators: The placeholder is the number of characters. */
			__( 'URL is %1$d characters long. Consider shortening it.', 'rank-math' )
	}
}

export default LengthPermalink
