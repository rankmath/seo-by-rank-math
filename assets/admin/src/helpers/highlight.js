/**
 * External dependencies
 */
import { truncate, isUndefined } from 'lodash'

/**
 * Internal dependencies
 */
import escapeRegex from '@helpers/escapeRegex'

export default function( keyword, text, length, separator ) {
	if ( '' === keyword || isUndefined( text ) ) {
		return text
	}

	// Truncate.
	text = truncate( text, {
		length,
		separator: separator || ' ',
	} )

	return text.replace(
		new RegExp( escapeRegex( keyword ), 'gi' ),
		( match ) => '<mark className="highlight">' + match + '</mark>'
	)
}
