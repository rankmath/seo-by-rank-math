/**
 * WordPress dependencies
 */
import { count } from '@wordpress/wordcount'

/**
 * Calculates the wordcount of a certain text.
 *
 * @param {string} text The text to be counted.
 *
 * @return {number} The word count of the given text.
 */

export default ( text ) => count( text, 'words' )
