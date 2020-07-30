/**
 * External dependencies
 */
import { get as _get } from 'lodash'

/**
 * Internal dependencies
 */
import decodeEntities from '@helpers/decodeEntities'

function getProgress( curLength, curWidth, settings ) {
	let widthPercent = 0
	if ( _get( settings, 'pixelWidth', false ) !== false ) {
		widthPercent = Math.min( 100, Math.floor( ( curWidth / settings.pixelWidth ) * 100 ) )
	}

	return (
		Math.max( Math.min( 100, Math.floor( ( curLength / settings.max ) * 100 ) ), widthPercent ) + '%'
	)
}

function isInvalidLength( curLength, settings ) {
	return curLength <= settings.min || curLength > settings.max
}

function isInvalidWidth( curWidth, settings ) {
	return curWidth <= settings.minWidth || curWidth > settings.pixelWidth
}

export default function( text, settings ) {
	const curLength = decodeEntities( text ).length
	let curWidth = 0
	let countWidth = false
	if ( _get( settings, 'pixelWidth', false ) !== false ) {
		countWidth = true
		let elemText = document.createTextNode( text )
		let elem = document.createElement( 'span' )
		elem.appendChild( elemText )
		elem.id = 'rank-math-width-tester'
		elem.className = settings.widthCheckerClass
		let widthTesterElem = document.body.appendChild( elem )
		curWidth = document.getElementById( 'rank-math-width-tester' ).offsetWidth
		widthTesterElem.outerHTML = ''
	}

	return {
		left: getProgress( curLength, curWidth, settings ),
		isInvalid: isInvalidLength( curLength, settings ),
		isInvalidWidth: countWidth ? isInvalidWidth( curWidth, settings ) : false,
		count: curLength + ' / ' + settings.max,
		pixelWidth: countWidth ? curWidth + 'px / ' + settings.pixelWidth + 'px' : '',
	}
}
