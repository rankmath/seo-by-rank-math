/**
 * External dependencies
 */
import { isUndefined, forEach } from 'lodash'

const diacriticsMap = {}
if ( ! isUndefined( rankMath.assessor ) ) {
	forEach( rankMath.assessor.diacritics, ( value, key ) => diacriticsMap[ key ] = new RegExp( value, 'g' ) )
}

export default ( text ) => {
	if ( isUndefined( text ) ) {
		return text
	}

	// Iterate through each keys in the above object and perform a replace
	for ( const x in diacriticsMap ) {
		text = text.replace( diacriticsMap[ x ], x )
	}

	return text
}
