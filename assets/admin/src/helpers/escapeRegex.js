/**
 * Escape the regex.
 *
 * @param {string} text String to escape
 *
 * @return {string} The escaped string.
 */
export default ( text ) => text.replace( /[\\^$*+?.()|[\]{}]/g, '\\$&' )
