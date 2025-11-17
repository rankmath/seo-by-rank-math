/**
 * Strip HTML-tags from text
 *
 * @param {string} text The string being counted.
 *
 * @return {string} The manipulated text.
 */
export default ( text ) => text.replace( /<\/?[a-z][^>]*?>/gi, '' )
