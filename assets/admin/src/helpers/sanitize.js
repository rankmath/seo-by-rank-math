/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Internal dependencies
 */
import { Helpers } from '@rankMath/analyzer'

/**
 * Sanitize the choices
 *
 * @param {Array} options The choices to sanitize.
 * @return {Array} The sanitized options.
 */
export function sanitizeChoices( options ) {
	return map( options, ( label, value ) => {
		return { label, value }
	} )
}

/**
 * Remove accents from a string
 *
 * @param {string} slug Current slug
 * @return {string} remove Aacents.
 */
export function sanitizePermalink( slug ) {
	// Remove commas from slug
	slug = slug.replace( /,/g, '' )

	// Remove diacritics/remove accents
	slug = Helpers.removeDiacritics( slug )

	// Sanitize text
	slug = Helpers.sanitizeText( slug )

	return slug
}
