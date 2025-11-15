/**
 * External dependencies
 */

import { filter } from 'lodash'

/**
 * Internal dependencies
 */
import wordCount from '@researches/wordCount'
import matchParagraphs from '@helpers/matchParagraphs'

/**
 * Gets all paragraphs and their word counts from the text.
 *
 * @param {string} content The text to get paragraphs from.
 *
 * @return {Array} The array containing an object with the paragraph word count and paragraph text.
 */
export default ( content ) => {
	const paragraphsLength = []
	matchParagraphs( content ).map( ( paragraph ) => paragraphsLength.push(
		{
			wordCount: wordCount( paragraph ),
			text: paragraph,
		}
	) )

	return filter( paragraphsLength, ( paragraphLength ) => 0 < paragraphLength.wordCount )
}
