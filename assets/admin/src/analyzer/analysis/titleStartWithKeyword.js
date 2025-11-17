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
import normalizeQuotes from '@helpers/normalizeQuotes'

class TitleStartWithKeyword extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Use the Focus Keyword near the beginning of SEO title.', 'rank-math' ) )
			.setTooltip( __( 'The SEO page title should contain the Focus Keyword preferably at the beginning.', 'rank-math' ) )
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
		const title = paper.getLower( 'title' )
		const keywordPosition = title.indexOf( normalizeQuotes( paper.getLower( 'keyword' ) ) )
		const titleHalfLength = Math.floor( title.length / 2 )
		const startWithKeyword = 0 <= keywordPosition && keywordPosition < titleHalfLength ? true : false

		analysisResult
			.setScore( this.calculateScore( startWithKeyword ) )
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
		return paper.hasKeyword() && paper.hasTitle()
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} startWithKeyword Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( startWithKeyword ) {
		return startWithKeyword ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_titleStartWithKeyword_score', 3 )
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
			__( 'Focus Keyword used at the beginning of SEO title.', 'rank-math' ) :
			__( 'Focus Keyword doesn\'t appear at the beginning of SEO title.', 'rank-math' )
	}
}

export default TitleStartWithKeyword
