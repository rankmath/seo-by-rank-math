// Replace all other punctuation characters at the beginning or at the end of a word.
const punctuationRegexString = '[\\–\\-\\(\\)_\\[\\]’“”"\'.?!:;,¿¡«»‹›\u2014\u00d7\u002b\u0026\<\>]+'
const punctuationRegexStart = new RegExp( '^' + punctuationRegexString )
const punctuationRegexEnd = new RegExp( punctuationRegexString + '$' )

/**
 * Replaces punctuation characters from the given text string.
 *
 * @param {string} text The text to remove the punctuation characters for.
 *
 * @return {string} The sanitized text.
 */
export default ( text ) => text
	.replace( punctuationRegexStart, '' )
	.replace( punctuationRegexEnd, '' )
