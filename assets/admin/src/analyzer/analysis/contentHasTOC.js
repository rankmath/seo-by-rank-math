/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __, sprintf } from '@wordpress/i18n'
import { applyFilters } from '@wordpress/hooks'

/**
 * Internal dependencies
 */
import getLink from '@helpers/getLink'
import Analysis from '@root/analyzer/Analysis'
import AnalysisResult from '@root/analyzer/AnalysisResult'

class ContentHasTOC extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Use Table of Content to break-down your text.', 'rank-math' ) )
			.setTooltip( __( 'Table of Contents help break down content into smaller, digestible chunks. It makes reading easier which in turn results in better rankings.', 'rank-math' ) )
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
		const hasTOCPlugin = rankMath.assessor.hasTOCPlugin || includes( paper.getTextLower(), 'wp-block-rank-math-toc-block' )

		analysisResult
			.setScore( this.calculateScore( hasTOCPlugin ) )
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
	 * @param {boolean} hasTOCPlugin Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasTOCPlugin ) {
		return hasTOCPlugin ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_contentHasTOC_score', 2 )
	}

	/**
	 * Translates the score to a message the user can understand.
	 *
	 * @param {AnalysisResult} analysisResult AnalysisResult with the score and the formatted text.
	 *
	 * @return {string} The translated string.
	 */
	translateScore( analysisResult ) {
		const link = getLink( 'toc', 'Content Analysis' )
		return analysisResult.hasScore() ?
			sprintf(
				/* Translators: Placeholder expands to "Table of Contents plugin" with a link to the corresponding KB article. */
				__( 'You seem to be using a %1$s to break-down your text.', 'rank-math' ),
				'<a href="' + link + '" target="_blank">Table of Contents plugin</a>'
			) :
			sprintf(
				/* Translators: Placeholder expands to "Table of Contents plugin" with a link to the corresponding KB article. */
				__( 'You don\'t seem to be using a %1$s.', 'rank-math' ),
				'<a href="' + link + '" target="_blank">Table of Contents plugin</a>'
			)
	}
}

export default ContentHasTOC
