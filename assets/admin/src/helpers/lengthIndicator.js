/**
 * Internal dependencies
 */
import decodeEntities from '@helpers/decodeEntities'

function getProgress( curLength, settings ) {
	return (
		Math.min( 100, Math.floor( ( curLength / settings.max ) * 100 ) ) + '%'
	)
}

function isInvalidLength( curLength, settings ) {
	return curLength <= settings.min || curLength > settings.max
}

export default function( text, settings ) {
	const curLength = decodeEntities( text ).length
	return {
		left: getProgress( curLength, settings ),
		isInvalid: isInvalidLength( curLength, settings ),
		count: curLength + ' / ' + settings.max,
	}
}
