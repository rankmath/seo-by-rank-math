/**
 * External dependencies
 */
import { includes } from 'lodash'

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

class KeywordInTitle extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @param {Paper} paper The paper to run this assessment on.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult( paper ) {
		return new AnalysisResult()
			.setMaxScore( this.getScore( paper.getShortLocale() ) )
			.setEmpty( __( 'Add Focus Keyword to the SEO title.', 'rank-math' ) )
			.setTooltip( __( 'Make sure the focus keyword appears in the SEO post title too.', 'rank-math' ) )
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
		const analysisResult = this.newResult( paper )

		const containKeyword = includes( paper.getLower( 'title' ), normalizeQuotes( paper.getLower( 'keyword' ) ) )

		analysisResult
			.setScore( this.calculateScore( containKeyword, paper ) )
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
	 * @param {boolean} hasKeyword Title has number or not.
	 * @param {Paper}   paper      The paper to use for the assessment.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasKeyword, paper ) {
		return hasKeyword ? this.getScore( paper.getShortLocale() ) : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @param {string} locale The paper locale.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore( locale ) {
		const score = 'en' === locale ? 36 : 38
		return applyFilters( 'rankMath_analysis_keywordInTitle_score', score )
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
			__( 'Hurray! You\'re using Focus Keyword in the SEO Title.', 'rank-math' ) :
			__( 'Focus Keyword does not appear in the SEO title.', 'rank-math' )
	}
}

export default KeywordInTitle
