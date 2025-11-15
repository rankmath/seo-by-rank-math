/**
 * External dependencies
 */
import { map } from 'lodash'

/**
 * Internal dependencies
 */
import { sanitizeText } from '@helpers/cleanText'
import removeDiacritics from '@helpers/removeDiacritics'

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
	slug = removeDiacritics( slug )

	// Sanitize text
	slug = sanitizeText( slug )

	return slug
}
