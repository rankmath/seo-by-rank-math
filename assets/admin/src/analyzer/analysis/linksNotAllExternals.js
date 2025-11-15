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

class LinksNotAllExternals extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Add DoFollow links pointing to external resources.', 'rank-math' ) )
			.setTooltip( __( 'PageRank Sculpting no longer works. Your posts should have a mix of nofollow and DoFollow links.', 'rank-math' ) )
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
		const linkStatistics = researcher.getResearch( 'getLinkStats' )
		const statistics = linkStatistics( paper.getText() )

		if ( 0 === statistics.total ) {
			analysisResult.setText( __( 'Add DoFollow links pointing to external resources.', 'rank-math' ) )
			return analysisResult
		}

		analysisResult
			.setScore( this.calculateScore( 0 < statistics.externalDofollow ) )
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
		return applyFilters( 'rankMath_analysis_linksNotAllExternals_score', 2 )
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
			__( 'At least one external link with DoFollow found in your content.', 'rank-math' ) :
			/* Translators: placeholder is the number of links. */
			__( 'We found %1$d outbound links in your content and all of them are nofollow.', 'rank-math' )
	}
}

export default LinksNotAllExternals
