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

class LinksHasExternals extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Link out to external resources.', 'rank-math' ) )
			.setTooltip( __( 'It helps visitors read more about a topic and prevents pogosticking.', 'rank-math' ) )
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
		const linkStatistics = researcher.getResearch( 'getLinkStats' )
		const statistics = linkStatistics( paper.getText() )

		if ( 0 === statistics.total ) {
			return analysisResult
		}

		analysisResult
			.setScore( this.calculateScore( 0 < statistics.externalTotal ) )
			.setText(
				sprintf(
					this.translateScore( analysisResult ),
					statistics.externalTotal
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
		return paper.hasText()
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} hasExternalDofollow Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasExternalDofollow ) {
		return hasExternalDofollow ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_linksHasExternals_score', 4 )
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
			__( 'Great! You are linking to external resources.', 'rank-math' ) :
			__( 'No outbound links were found. Link out to external resources.', 'rank-math' )
	}
}

export default LinksHasExternals
