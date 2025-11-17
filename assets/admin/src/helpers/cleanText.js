/**
 * External dependencies
 */
import { flow, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import stripTags from '@researches/stripTags'
import stripStyle from '@helpers/stripStyles'
import stripSpaces from '@helpers/stripSpaces'
import stripScript from '@helpers/stripScripts'
import normalizeQuotes from '@helpers/normalizeQuotes'
import normalizeWhitespace from '@helpers/normalizeWhitespace'
import stripHTMLComments from '@helpers/stripHTMLComments'
import stripHTMLEntities from '@helpers/stripHTMLEntities'

/**
 * Clean HTML strip all html entities and comments.
 *
 * @param {string} text Text to clean.
 *
 * @return {string} The clean text.
 */
export function cleanHTML( text ) {
	return isUndefined( text ) ? '' : flow(
		[
			normalizeWhitespace,
			stripStyle,
			stripScript,
			stripHTMLComments,
			stripHTMLEntities,
			stripSpaces,
			normalizeQuotes,
		]
	)( text )
}

/**
 * Clean text strip out all html tags, entities and comments.
 *
 * @param {string} text Text to clean.
 *
 * @return {string} The clean text.
 */
export function cleanText( text ) {
	return isUndefined( text ) || ! text ? '' : flow(
		[
			stripStyle,
			stripScript,
			stripTags,
			stripHTMLComments,
			stripHTMLEntities,
			stripSpaces,
			normalizeQuotes,
		]
	)( text )
}

/**
 * Clean text strip out all html tags, entities and comments.
 *
 * @param {string} text Text to clean.
 *
 * @return {string} The clean text.
 */
export function cleanTagsOnly( text ) {
	return isUndefined( text ) ? '' : flow(
		[
			stripTags,
			stripSpaces,
		]
	)( text )
}

/**
 * Strip double spaces from text
 *
 * @param {string} text The text to strip spaces from.
 *
 * @return {string} The text without double spaces
 */
function stripSpaces2( text ) {
	return text
		.replace( /&nbsp;|&#160;/gi, ' ' )
		.replace( /\s{2,}/g, ' ' ) // Replace multiple spaces with single space
		.replace( /\s\./g, '.' ) // Replace spaces followed by periods with only the period.
		.replace( /(\r\n|\n|\r)/gm, '' )
}

/**
 * Clean text strip out all html tags, extra spaces, entities and comments.
 *
 * @param {string} text Text to clean.
 *
 * @return {string} The clean text.
 */
export function sanitizeText( text ) {
	return isUndefined( text ) ? '' : flow(
		[
			stripStyle,
			stripScript,
			stripTags,
			stripHTMLComments,
			stripSpaces2,
		]
	)( text )
}

/**
 * Clean text strip out all html tags, entities and comments.
 *
 * @param {string} text Text to clean.
 *
 * @return {string} The clean text.
 */
export function sanitizeAppData( text ) {
	return isUndefined( text ) ? '' : flow(
		[
			stripStyle,
			stripScript,
			stripTags,
			stripHTMLComments,
		]
	)( text )
}
