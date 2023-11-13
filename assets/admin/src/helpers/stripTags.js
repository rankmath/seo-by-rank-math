/* global DOMParser */

/**
 * External dependencies
 */
import jQuery from 'jquery'

/**
 * Function to strip all tags from the given html.
 *
 * @param {string} html string to strip the tags from.
 *
 * @return {string} String after removing the tags.
 */
export default ( html ) => {
	// First decode.
	html = jQuery( '<textarea />' ).html( html ).text()

	// Strip tags.
	const doc = new DOMParser().parseFromString( html, 'text/html' )
	const output = doc.body.textContent || ''

	// Strip remaining characters.
	return output.replace( /["<>]/g, '' ) || ''
}
