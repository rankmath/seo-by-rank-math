/**
 * External dependencies
 */
import { forEach, round } from 'lodash'

/**
 * Internal dependencies
 */
import replaceInvalid from '@helpers/replaceInvalid'

/**
 * Analysis result manager.
 */
class ResultManager {
	/**
	 * Class constructor.
	 */
	constructor() {
		this.results = new Map
	}

	/**
	 * Get all results.
	 *
	 * @return {Object} Results.
	 */
	getResults() {
		return Object.fromEntries( this.results )
	}

	/**
	 * Get result for keyword.
	 *
	 * @param {string} keyword Keyword for which you want score.
	 *
	 * @return {AnalysisResult} Analysis results for keyword.
	 */
	getResult( keyword ) {
		return this.results.get( this.cleanText( keyword ) )
	}

	/**
	 * Get the available score.
	 *
	 * @param {string} keyword Keyword for which you want score.
	 *
	 * @return {number} Result score.
	 */
	getScore( keyword ) {
		const kw = this.cleanText( keyword )
		if ( this.results.has( kw ) ) {
			return this.results.get( kw ).score
		}

		return 0
	}

	/**
	 * Add result.
	 *
	 * @param {string}         keyword   Keyword of which results are.
	 * @param {AnalysisResult} results   Analysis results.
	 * @param {boolean}        isPrimary Is primary keyword.
	 */
	update( keyword, results, isPrimary = false ) {
		const kw = this.cleanText( keyword )
		if ( this.results.has( kw ) ) {
			const oldResults = this.results.get( kw )
			results = {
				...oldResults.results,
				...results,
			}
			isPrimary = oldResults.isPrimary
		}

		this.results.set(
			kw,
			{
				results,
				isPrimary,
				score: this.refreshScore( results ),
			}
		)
	}

	/**
	 * Refresh score after results update.
	 *
	 * @param {AnalysisResult} results Analysis results.
	 *
	 * @return {number} Analysis total score.
	 */
	refreshScore( results ) {
		let score = 0
		let total = 0
		const shortLocale = rankMath.localeFull.split( '_' )[ 0 ]

		forEach( results, ( result ) => {
			score += result.getScore()
			total += result.getMaxScore( shortLocale )
		} )

		return round( ( score / total ) * 100 )
	}

	/**
	 * Delete result for keyword.
	 *
	 * @param {string} keyword Keyword for which you want score.
	 */
	deleteResult( keyword ) {
		this.results.delete( this.cleanText( keyword ) )
	}

	/**
	 * Check if keyword is primary.
	 *
	 * @param {string} keyword Keyword for which you want score.
	 *
	 * @return {boolean} Whether keyword is primary or not.
	 */
	isPrimary( keyword ) {
		const kw = this.cleanText( keyword )
		if ( this.results.has( this.cleanText( kw ) ) ) {
			return this.results.get( this.cleanText( kw ) ).isPrimary
		}

		return false
	}

	/**
	 * Cleans the parsed text to match class Paper.
	 *
	 * @param {string} text The text to clean.
	 *
	 * @return {string|*} The cleaned text.
	 */
	cleanText( text ) {
		return replaceInvalid( text )
	}
}

export default ResultManager
