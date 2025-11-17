/**
 * External dependencies
 */
import Sentiment from 'sentiment'

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
import sentimentWords from '@config/sentimentWords'

class TitleSentiment extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @param {Paper} paper The paper to run this assessment on.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult( paper ) {
		return 'en' !== paper.getShortLocale() ? null : new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Titles with positive or negative sentiment work best for higher CTR.', 'rank-math' ) )
			.setTooltip( __( 'Headlines with a strong emotional sentiment (positive or negative) tend to receive more clicks.', 'rank-math' ) )
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
		const sentiment = new Sentiment
		const sentimentScore = sentiment.analyze( paper.getLower( 'title' ), sentimentWords ).score

		analysisResult
			.setScore( this.calculateScore( sentimentScore ) )
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
		return 'en' === paper.getShortLocale() && paper.hasTitle()
	}

	/**
	 * Calculates the score based on the sentiment score.
	 *
	 * @param {boolean} sentimentScore Sentiment score.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( sentimentScore ) {
		return 0 !== sentimentScore ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_titleSentiment_score', 1 )
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
			__( 'Your title has a positive or a negative sentiment.', 'rank-math' ) :
			sprintf(
				/* Translators: placeholder is the words "positive or a negative sentiment" as a link to the corresponding KB article. */
				__( 'Your title doesn\'t contain a %1$s word.', 'rank-math' ),
				'<a href="https://rankmath.com/kb/score-100-in-tests/#sentiment-in-a-title" target="_blank">positive or a negative sentiment</a>'
			)
	}
}

export default TitleSentiment
