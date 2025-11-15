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
import escapeRegex from '@helpers/escapeRegex'
import normalizeQuotes from '@helpers/normalizeQuotes'

class KeywordInSubheadings extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Use Focus Keyword in subheading(s) like H2, H3, H4, etc..', 'rank-math' ) )
			.setTooltip( __( 'It is recommended to add the focus keyword as part of one or more subheadings in the content.', 'rank-math' ) )
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
		const subheadingRegex = new RegExp( '<h[2-6][^>]*>.*' + escapeRegex( normalizeQuotes( paper.getLower( 'keyword' ) ) ) + '.*</h[2-6]>', 'gi' )
		const hasKeyword = null !== paper.getTextLower().match( subheadingRegex ) ? true : false
		
		analysisResult
			.setScore( this.calculateScore( hasKeyword ) )
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
		return paper.hasKeyword() && paper.hasText()
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} hasKeyword Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasKeyword ) {
		return hasKeyword ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_keywordInSubheadings_score', 3 )
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
			__( 'Focus Keyword found in the subheading(s).', 'rank-math' ) :
			__( 'Focus Keyword not found in subheading(s) like H2, H3, H4, etc..', 'rank-math' )
	}
}

export default KeywordInSubheadings
