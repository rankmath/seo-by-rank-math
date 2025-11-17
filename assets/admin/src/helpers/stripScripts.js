/**
 * Removes <script> tags from text.
 *
 * @param {string} text The string to remove from.
 * @param {string} replaceValue The string to replace the stripped contents with.
 *
 * @return {string} The manipulated text.
 */
export default ( text, replaceValue = '' ) => text.replace( /<script[^>]*>([\S\s]*?)<\/script>/gim, replaceValue )
