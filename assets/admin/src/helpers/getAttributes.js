/**
 * Internal dependencies
 */
import stripTags from './stripTags'

/**
 * Function to strip all tags from the given html.
 *
 * @param {Object} data Attributes data in array.
 *
 * @return {string} attributes data.
 */
export default ( data ) => {
	// only items which are objects have properties which can be used as attributes
	if ( Object.prototype.toString.call( data ) !== '[object Object]' ) {
		return ''
	}

	let attrs = '',
		propName,
		i

	const keys = Object.keys( data )
	for ( i = keys.length; i--; ) {
		propName = keys[ i ]
		if ( propName !== 'class' && data.hasOwnProperty( propName ) && data[ propName ] ) {
			attrs += '' + propName + ( data[ propName ] ? '=\"'.concat( stripTags( data[ propName ] ), '\"' ) : '' )
		}
	}

	return attrs
}
