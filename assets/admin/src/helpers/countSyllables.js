/**
 * External dependencies
 */
import { map, sum } from 'lodash'

/**
 * Count syllables
 *
 * @see {@link https://medium.com/@andrewhartnett/to-parse-a-haiku-using-only-javascript-was-interesting-5ea64ce31948}
 *
 * @param {string} word Word to look for syllables.
 *
 * @return {number} Number of syllables in word.
 */
function countSyllablesInWord( word ) {
	word = word.toLowerCase()
	if ( 3 >= word.length ) {
		return 1
	}

	word = word.replace( /(?:[^laeiouy]es|ed|lle|[^laeiouy]e)$/, '' )
		.replace( /^y/, '' )
		.match( /[aeiouy]{1,2}/g )

	return null === word ? 0 : word.length
}

export default ( words ) => {
	const syllableCounts = map( words, ( word ) => countSyllablesInWord( word ) )

	return sum( syllableCounts )
}
