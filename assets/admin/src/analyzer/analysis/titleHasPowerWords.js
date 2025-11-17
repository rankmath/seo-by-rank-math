/**
 * External dependencies
 */
import { indexOf, isEmpty } from 'lodash'

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
import removeDiacritics from '@helpers/removeDiacritics'

class TitleHasPowerWords extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return ! this.hasPowerWords() ? null : new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty(
				sprintf(
					/* Translators: placeholder is the words "power words" as a link to the corresponding KB article. */
					__( 'Add %s to your title to increase CTR.', 'rank-math' ),
					'<a href="https://rankmath.com/blog/power-words/" target="_blank">power words</a>'
				)
			)
			.setTooltip( __( 'Power Words are tried-and-true words that copywriters use to attract more clicks.', 'rank-math' ) )
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
		const title = paper.getLower( 'title' ).split( ' ' )
		const powerWordsInText = rankMath.assessor.powerWords.filter( ( word ) => indexOf( title, removeDiacritics( word ) ) >= 0 )
		const hasPowerWords = 0 < powerWordsInText.length

		analysisResult
			.setScore( this.calculateScore( hasPowerWords ) )
			.setText(
				sprintf(
					this.translateScore( analysisResult ),
					powerWordsInText.length
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
		return this.hasPowerWords() && paper.hasTitle()
	}

	/**
	 * Is power words availablel for current site language.
	 *
	 * @return {boolean} True if available.
	 */
	hasPowerWords() {
		return ! isEmpty( rankMath.assessor.powerWords )
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} hasPowerWords Title has power words or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( hasPowerWords ) {
		return hasPowerWords ? this.getScore() : null
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return applyFilters( 'rankMath_analysis_titleHasPowerWords_score', 1 )
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
			/* Translators: placeholder is the number of power words. */
			__( 'Your title contains %1$d power word(s). Booyah!', 'rank-math' ) :
			sprintf(
				/* Translators: placeholder is the words "power word" as a link to the corresponding KB article. */
				__( 'Your title doesn\'t contain a %1$s. Add at least one.', 'rank-math' ),
				'<a href="https://rankmath.com/blog/power-words/" target="_blank">power word</a>'
			)
	}
}

export default TitleHasPowerWords
