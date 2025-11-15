/**
 * External dependencies
 */
import { uniq, includes, isUndefined } from 'lodash'

export default ( plurals ) => {
	const words = [],
		values = []
	plurals.forEach( ( data ) => {
		words.push( data.word )
		values.push( data.plural )
	} )

	const length = words.length,
		output = []

	output.push( words.join( ' ' ) )

	function recursive( recursiveWords ) {
		plurals.forEach( ( data ) => {
			if ( data.plural === data.word || includes( recursiveWords, data.plural ) ) {
				return
			}
			output.push( recursiveWords.join( ' ' ).replace( data.word, data.plural ) )
		} )
	}

	for ( let i = 0; i < ( length * length ); i++ ) {
		if ( ! isUndefined( output[ i ] ) ) {
			recursive( output[ i ].split( ' ' ) )
		}
	}
	output.push( values.join( ' ' ) )

	return uniq( output )
}
