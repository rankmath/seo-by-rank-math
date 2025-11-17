/**
 * External dependencies
 */
import { has, isEmpty, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import slugify from '@researches/slugify'
import getWords from '@researches/getWords'
import Pluralize from '@researches/pluralize'
import stripTags from '@researches/stripTags'
import wordCount from '@researches/wordCount'
import combinations from '@researches/combinations'
import getLinkStats from '@researches/getLinkStats'
import fleschReading from '@researches/fleschReading'
import getParagraphs from '@researches/getParagraphs'
import removePunctuation from '@researches/removePunctuation'

/**
 * Researcher class
 */
class Researcher {
	/**
	 * Class constructor.
	 *
	 * @param {Paper} paper The paper to use for the assessment.
	 */
	constructor( paper ) {
		this.setPaper( paper )
		this.researches = {
			combinations,
			fleschReading,
			getLinkStats,
			getParagraphs,
			getWords,
			pluralize: new Pluralize,
			removePunctuation,
			slugify,
			stripTags,
			wordCount,
		}
	}

	/**
	 * Set the Paper.
	 *
	 * @param {Paper} paper The paper
	 */
	setPaper( paper ) {
		this.paper = paper
	}

	/**
	 * Get all researches.
	 *
	 * @return {Object} An object containing all available researches.
	 */
	getResearches() {
		return this.researches
	}

	/**
	 * Return the Research by name.
	 *
	 * @param {string} name The name to reference the research by.
	 *
	 * @return {*} Returns the result of the research or false if research does not exist.
	 */
	getResearch( name ) {
		if ( isUndefined( name ) || isEmpty( name ) ) {
			return false
		}

		if ( ! this.hasResearch( name ) ) {
			return false
		}

		return this.getResearches()[ name ]
	}

	/**
	 * Check whether or not the research is known by the Researcher.
	 *
	 * @param {string} name The name to reference the research by.
	 *
	 * @return {boolean} Whether or not the research is known by the Researcher
	 */
	hasResearch( name ) {
		return has( this.getResearches(), name )
	}
}

export default Researcher
