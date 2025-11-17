/**
 * External dependencies
 */
import { filter, flow, map } from 'lodash'

/**
 * Internal dependencies
 */
import stripTags from '@researches/stripTags'
import stripSpaces from '@helpers/stripSpaces'
import stripShortcodes from '@helpers/stripShortcodes'
import stripConnectors from '@helpers/stripConnectors'
import stripRemovables from '@helpers/stripRemovables'
import stripHTMLComments from '@helpers/stripHTMLComments'
import stripHTMLEntities from '@helpers/stripHTMLEntities'
import removePunctuation from '@researches/removePunctuation'

/**
 * Returns an array with words used in the text.
 *
 * @param {string} text The text to be counted.
 *
 * @return {Array} The array with all words.
 */
const getWords = ( text ) => {
	text = flow(
		[
			stripTags,
			stripHTMLComments,
			stripShortcodes,
			stripSpaces,
			stripHTMLEntities,
			stripConnectors,
			stripRemovables,
		]
	)( text )

	if ( '' === text ) {
		return []
	}

	let words = text.split( /\s/g )
	words = map( words, ( word ) => removePunctuation( word ) )

	return filter( words, ( word ) => '' !== word.trim() )
}

/**
 * Returns an array with words used in the text.
 *
 * @param {string} text  The text to be counted.
 * @param {number} limit The number of words required.
 *
 * @return {Array} The array with all words.
 */
export default ( text, limit ) => {
	const words = getWords( text )
	limit = limit || false

	if ( 0 === words.length ) {
		return false
	}

	if ( false === limit ) {
		return words
	}

	return words.slice( 0, limit )
}
