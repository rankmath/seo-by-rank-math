/**
 * Converts the first letter of each word in the parsed string to uppercase.
 *
 * @param {string} str The text to capitalize
 *
 * @return {string}
 */

export function capitalizeString( str ) {
	return str.replace( /(^\w{1})|(\s+\w{1})/g, ( letter ) => letter.toUpperCase() )
}
