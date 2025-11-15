/**
 * External dependencies
 */
import { map, inRange, isEmpty } from 'lodash'

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
import { cleanTagsOnly } from '@helpers/cleanText'
import normalizeQuotes from '@helpers/normalizeQuotes'

class KeywordDensity extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Keyword Density is 0. Aim for around 1% Keyword Density.', 'rank-math' ) )
			.setTooltip( __( 'There is no ideal keyword density percentage, but it should not be too high. The most important thing is to keep the copy natural.', 'rank-math' ) )
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
		const getWordCount = researcher.getResearch( 'wordCount' )
		const wordCount = getWordCount( paper.getTextLower() )
		const keywords = paper.get( 'keywords' ).map( ( keyword ) => normalizeQuotes( keyword ) )

		if ( false === wordCount || 0 === wordCount || isEmpty( keywords ) ) {
			return analysisResult
		}

		// Keyword Density & Focus Keyword occurrence
		const regex = new RegExp( map( keywords, escapeRegex ).join( '|' ), 'gi' )
		const count = ( cleanTagsOnly( paper.getText() ).match( regex ) || [] ).length
		const keywordDensity = applyFilters( 'rankMath_analysis_keywordDensity', ( ( count / wordCount ) * 100 ).toFixed( 2 ), count )
		const calculatedScore = this.calculateScore( keywordDensity )

		analysisResult
			.setScore( calculatedScore.score )
			.setText(
				sprintf(
					this.translateScore( calculatedScore.type ),
					keywordDensity,
					count
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
	 * Translates the score to a message the user can understand.
	 *
	 * @param {string} type Type of score.
	 *
	 * @return {string} The translated string.
	 */
	translateScore( type ) {
		if ( 'low' === type ) {
			/* Translators: 1: keyword density number, 2: number of times the Focus Keyword appears in he content. */
			return __( 'Keyword Density is %1$s which is low, the Focus Keyword and combination appears %2$s times.', 'rank-math' )
		}

		if ( 'high' === type ) {
			/* Translators: 1: keyword density number, 2: number of times the Focus Keyword appears in he content. */
			return __( 'Keyword Density is %1$s which is high, the Focus Keyword and combination appears %2$s times.', 'rank-math' )
		}

		/* Translators: 1: keyword density number, 2: number of times the Focus Keyword appears in he content. */
		return __( 'Keyword Density is %1$s, the Focus Keyword and combination appears %2$s times.', 'rank-math' )
	}

	/**
	 * Calculates the score based on the url length.
	 *
	 * @param {boolean} keywordDensity Title has number or not.
	 *
	 * @return {number} The calculated score.
	 */
	calculateScore( keywordDensity ) {
		const scores = this.getBoundaries()

		if ( 0.5 > keywordDensity ) {
			return {
				type: 'low',
				score: scores.fail,
			}
		}

		if ( 2.5 < keywordDensity ) {
			return {
				type: 'high',
				score: scores.fail,
			}
		}

		if ( inRange( keywordDensity, 0.5, 0.75 ) ) {
			return {
				type: 'fair',
				score: scores.fair,
			}
		}

		if ( inRange( keywordDensity, 0.76, 1.0 ) ) {
			return {
				type: 'good',
				score: scores.good,
			}
		}

		return {
			type: 'best',
			score: scores.best,
		}
	}

	/**
	 * Get analysis max score.
	 *
	 * @return {number} Max score an analysis has
	 */
	getScore() {
		return this.getBoundaries().best
	}

	getBoundaries() {
		return applyFilters(
			'rankMath_analysis_keywordDensity_score',
			{
				fail: 0,
				fair: 2,
				good: 3,
				best: 6,
			}
		)
	}
}

export default KeywordDensity
