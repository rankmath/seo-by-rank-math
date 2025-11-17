/**
 * External dependencies
 */
import { flow, filter } from 'lodash'
import { ParseEnglish } from 'parse-english'

/**
 * Internal dependencies
 */
import stripTags from '@researches/stripTags'
import stripHTMLComments from '@helpers/stripHTMLComments'
import stripShortcodes from '@helpers/stripShortcodes'
import stripSpaces from '@helpers/stripSpaces'
import stripHTMLEntities from '@helpers/stripHTMLEntities'
import stripConnectors from '@helpers/stripConnectors'

/**
 * Count Sentences
 *
 * @param {string} text Text to count sentences.
 *
 * @return {number} Count of sentences.
 */
export default ( text ) => {
	text = flow(
		[
			stripTags,
			stripHTMLComments,
			stripShortcodes,
			stripSpaces,
			stripHTMLEntities,
			stripConnectors,
		]
	)( text )

	if ( '' === text ) {
		return 0
	}

	const paragraphs = new ParseEnglish().tokenizeParagraph( text ).children
	return filter( paragraphs, { type: 'SentenceNode' } ).length
}
