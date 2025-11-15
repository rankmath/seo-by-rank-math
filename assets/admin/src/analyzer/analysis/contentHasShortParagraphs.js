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

class ContentHasShortParagraphs extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Add short and concise paragraphs for better readability and UX.', 'rank-math' ) )
			.setTooltip( __( 'Short paragraphs are easier to read and more pleasing to the eye. Long paragraphs scare the visitor, and they might result to SERPs looking for better readable content.', 'rank-math' ) )
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
		const getParagraphs = researcher.getResearch( 'getParagraphs' )
		const hasBigParagraphs = getParagraphs( paper.getText() )
			.some( ( paragraph ) => 120 < paragraph.wordCount )

		analysisResult
			.setScore( this.calculateScore( hasBigParagraphs ) )
			.setText( this.translateScore( analysisResult ) )

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
	 * @param {boolean} hasBigParagraphs Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasBigParagraphs ) {
		return hasBigParagraphs ? null : this.getScore()
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_contentHasShortParagraphs_score', 3 )
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
			__( 'You are using short paragraphs.', 'rank-math' ) :
			__( 'At least one paragraph is long. Consider using short paragraphs.', 'rank-math' )
	}
}

export default ContentHasShortParagraphs
