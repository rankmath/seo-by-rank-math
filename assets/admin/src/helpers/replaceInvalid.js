/**
 * External dependencies
 */
import { isUndefined } from 'lodash'

/**
 * Replaces known invalid characters with an ordinary space.
 *
 * @param {string} text The text to strip invalid chars from.
 *
 * @return {string} The text without invalid chars.
 */
export default ( text ) =>  {
	if ( isUndefined( text ) ) {
		return text
	}

	return text.replace( /\xA0/gu, ' ' )
}
