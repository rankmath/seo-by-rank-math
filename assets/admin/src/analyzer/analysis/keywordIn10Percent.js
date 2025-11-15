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

class KeywordIn10Percent extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Use Focus Keyword at the beginning of your content.', 'rank-math' ) )
			.setTooltip( __( 'The first 10% of the content should contain the Focus Keyword preferably at the beginning.', 'rank-math' ) )
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
		/* eslint @wordpress/no-unused-vars-before-return: 0*/
		const analysisResult = this.newResult()
		const getWords = researcher.getResearch( 'getWords' )
		let words = getWords( paper.getTextLower() )

		if ( false === words ) {
			return analysisResult
		}

		if ( 400 < words.length ) {
			words = words.slice( 0, Math.floor( words.length * 0.1 ) )
		}
		words = words.join( ' ' )

		const keyword = getWords( normalizeQuotes( paper.getLower( 'keyword' ) ) ).join( ' ' )
		const hasKeyword = includes( words, keyword )

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
		return applyFilters( 'rankMath_analysis_keywordIn10Percent_score', 3 )
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
			__( 'Focus Keyword appears in the first 10% of the content.', 'rank-math' ) :
			__( 'Focus Keyword doesn\'t appear at the beginning of your content.', 'rank-math' )
	}
}

export default KeywordIn10Percent
