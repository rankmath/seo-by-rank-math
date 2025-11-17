/**
 * Removes items matched in the regex.
 *
 * @param {string} text The string being counted.
 *
 * @return {string} The manipulated text.
 */
export default ( text ) => text.replace( /<!--[\s\S]*?-->/g, '' )
