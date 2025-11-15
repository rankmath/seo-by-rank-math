/**
 * External dependencies
 */
import { includes, uniq, isNull } from 'lodash'

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

class KeywordInImageAlt extends Analysis {
	/**
	 * Create new analysis result instance.
	 *
	 * @return {AnalysisResult} New instance.
	 */
	newResult() {
		return new AnalysisResult()
			.setMaxScore( this.getScore() )
			.setEmpty( __( 'Add an image with your Focus Keyword as alt text.', 'rank-math' ) )
			.setTooltip( __( 'It is recommended to add the focus keyword in the alt attribute of one or more images.', 'rank-math' ) )
	}

	/**
	 * Executes the assessment and return its result
	 *
	 * @param {Paper} paper The paper to run this assessment on.
	 *
	 * @return {AnalysisResult} an AnalysisResult with the score and the formatted text.
	 */
	getResult( paper ) {
		const analysisResult = this.newResult()
		const thumbnailAlt = paper.getLower( 'thumbnailAlt' )
		let keyword = paper.getLower( 'keyword' )

		if ( keyword === thumbnailAlt || includes( thumbnailAlt, keyword ) ) {
			analysisResult
				.setScore( this.calculateScore( true ) )
				.setText( this.translateScore( analysisResult ) )

			return analysisResult
		}

		// Remove duplicate words from keyword.
		keyword = normalizeQuotes( uniq( keyword.split( ' ' ) ).join( ' ' ) )

		// Check if keyword is in alt text.
		let regex = /<img[^>]*\salt=(["'])(.*?)\1/gi
		let match
		const altTags = []
		const content = paper.getTextLower()
		while ( ! isNull( match = regex.exec( content ) ) ) {
			altTags.push( match[ 2 ] )
		}

		const keywordPattern = keyword.replace( /[.*+?^${}()|[\]\\]/g, '\\$&' ).replace( / /g, '.*' )
		if ( null !== altTags ) {
			for ( let i = 0; i < altTags.length; i++ ) {
				const altTag = altTags[ i ]
				const altTagMatch = altTag.match( new RegExp( keywordPattern, 'gi' ) )
				if ( null !== altTagMatch ) {
					analysisResult
						.setScore( this.calculateScore( true ) )
						.setText( this.translateScore( analysisResult ) )

					return analysisResult
				}
			}
		}

		regex = new RegExp( '\\[gallery( [^\\]]+?)?\\]', 'ig' )
		const hasGallery = null !== content.match( regex )

		if ( hasGallery ) {
			analysisResult
				.setScore( this.calculateScore( true ) )
				.setText( __( 'We detected a gallery in your content & assuming that you added Focus Keyword in alt in at least one of the gallery images.', 'rank-math' ) )
		}

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
		return paper.hasKeyword() && ( paper.hasText() || paper.hasThumbnailAltText() )
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
		return applyFilters( 'rankMath_analysis_keywordInImageAlt_score', 2 )
	}

	/**
	 * Translates the score to a message the user can understand.
	 *
	 * @param {AnalysisResult} analysisResult AnalysisResult with the score and the formatted text.
	 *
	 * @return {string} The translated string.
	 */
	translateScore( analysisResult ) {
		return analysisResult.hasScore()
			? __( 'Focus Keyword found in image alt attribute(s).', 'rank-math' )
			: __( 'Focus Keyword not found in image alt attribute(s).', 'rank-math' )
	}
}

export default KeywordInImageAlt
