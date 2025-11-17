/**
 * Strip double spaces from text
 *
 * @param {string} text The text to strip spaces from.
 *
 * @return {string} The text without double spaces
 */
export default ( text ) => text
	.replace( /&nbsp;|&#160;/gi, ' ' )
	.replace( /\s{2,}/g, ' ' ) // Replace multiple spaces with single space
	.replace( /\s\./g, '.' ) // Replace spaces followed by periods with only the period.
	.replace( /^\s+|\s+$/g, '' ) // Remove first/last character if space
