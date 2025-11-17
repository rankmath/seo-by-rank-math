/**
 * External dependencies
 */
import { deburr, toLower, trim } from 'lodash'

/**
 * Sanitize and slugify text
 *
 * @param {string} text The string being slugify.
 *
 * @return {string} The manipulated text.
 */
export default ( text ) => {
	if ( ! text ) {
		return ''
	}

	return toLower(
		deburr( trim( text.replace( /[\s\./_]+/g, '-' ), '-' ) )
	)
}
