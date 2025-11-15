/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'
import { cleanForSlug } from '@wordpress/url'

/**
 * Internal dependencies
 */
import Analysis from '@root/analyzer/Analysis'
import AnalysisResult from '@root/analyzer/AnalysisResult'

class KeywordInPermalink extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Use Focus Keyword in the URL.', 'rank-math' ) )
			.setTooltip( __( 'Include the focus keyword in the slug (permalink) of this post.', 'rank-math' ) )
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
		const permalink = paper.getUrl().replace( /[-_]/ig, '-' )
		const hasKeyword = includes( permalink, cleanForSlug( paper.getKeywordPermalink( researcher ) ) ) ||
			includes( permalink, cleanForSlug( paper.getPermalinkWithStopwords( researcher ) ) )

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
		return paper.hasKeyword() && paper.hasPermalink()
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
		return applyFilters( 'rankMath_analysis_keywordInPermalink_score', 5 )
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
			__( 'Focus Keyword used in the URL.', 'rank-math' ) :
			__( 'Focus Keyword not found in the URL.', 'rank-math' )
	}
}

export default KeywordInPermalink
