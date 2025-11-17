/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import Analysis from '@root/analyzer/Analysis'
import AnalysisResult from '@root/analyzer/AnalysisResult'

class isReviewEnabled extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Reviews are disabled on this Product.', 'rank-math' ) )
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
		const hasReview = rankMath.assessor.isReviewEnabled
		analysisResult
			.setScore( this.calculateScore( hasReview ) )
			.setText( this.translateScore( analysisResult ) )

		return analysisResult
	}

	/**
	 * Checks whether paper meet analysis requirements.
	 *
	 * @return {boolean} True when requirements meet.
	 */
	isApplicable() {
		return rankMath.assessor.isReviewEnabled
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} hasReview Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasReview ) {
		return hasReview ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_isReviewEnabled_score', 2 )
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
			__( 'Reviews are enabled for this Product. Good Job!', 'rank-math' ) :
			__( 'Reviews are disabled on this Product.', 'rank-math' )
	}
}

export default isReviewEnabled
