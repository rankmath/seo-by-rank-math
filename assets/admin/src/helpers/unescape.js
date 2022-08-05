/**
 * @copyright Copyright JS Foundation and other contributors <https://js.foundation/>
 *
 * The following code is a derivative work of the code from the Lodash unescape(https://github.com/lodash/lodash/blob/master/unescape.js), which is licensed under the MIT License.
 */

/** Used to map HTML entities to characters. */
const htmlUnescapes = {
	'&amp;': '&',
	'&quot;': '"',
	'&#39;': "'",
}

/** Used to match HTML entities and HTML characters. */
const reEscapedHtml = /&(?:amp|quot|#(0+)?39);/g
const reHasEscapedHtml = RegExp( reEscapedHtml.source )

/**
 * The inverse of `escape` this method converts the HTML entities
 * `&amp;`, `&quot;` and `&#39;` in `string` to
 * their corresponding characters.
 *
 * unescape('fred, barney, &amp; pebbles')
 * // => 'fred, barney, & pebbles'
 *
 * @param {string} [string=''] The string to unescape.
 * @return {string} Returns the unescaped string.
 */
function unescape( string ) {
	return ( string && reHasEscapedHtml.test( string ) )
		? string.replace( reEscapedHtml, ( entity ) => ( htmlUnescapes[ entity ] || "'" ) )
		: ( string || '' )
}

export default unescape
