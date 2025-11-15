/**
 * Removes <style> tags from text.
 *
 * @param {string} text The string to remove from.
 *
 * @return {string} The manipulated text.
 */
export default ( text ) => text.replace( /<style[^>]*>([\S\s]*?)<\/style>/gim, '' )
