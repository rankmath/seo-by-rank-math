/**
 * External dependencies
 */
import { get } from 'lodash'
import { Link } from 'react-router-dom'

/**
 * WordPress dependencies
 */
import { createPortal, useState, useEffect, useRef, Fragment } from '@wordpress/element'
import { decodeEntities } from '@wordpress/html-entities'

/**
 * Internal dependencies
 */
import IndexingDataFooter from './IndexingDataFooter'
import IndexingDataToggle from './IndexingDataToggle'

const clickHandler = ( event ) => {
	const nextSibling = event.currentTarget.nextSibling
	const childRow = nextSibling.classList.contains( 'inner-elements' ) ? nextSibling : nextSibling.querySelector( '.inner-elements' )
	if ( childRow.classList.contains( 'hidden' ) ) {
		childRow.classList.remove( 'hidden' )
		return
	}

	childRow.classList.add( 'hidden' )
}

const IndexingRow = ( { row } ) => {
	const [ col, setCol ] = useState( null )
	const [ elemReady, setElemReady ] = useState( false )
	const inputReference = useRef( null )

	useEffect( () => {
		inputReference.current.click()
	}, [] )

	const indexingData = ( event ) => {
		if ( elemReady ) {
			return
		}

		event.preventDefault()
		if ( ! elemReady ) {
			const elem = document.createElement( 'tr' )
			elem.classList.add( 'rank-math-child-row' )
			const tr = event.currentTarget.closest( 'tr' )
			tr.addEventListener( 'click', clickHandler )
			tr.parentNode.insertBefore( elem, tr.nextSibling )

			const td = document.createElement( 'td' )
			td.colSpan = 10
			elem.appendChild( td )

			setCol( td )
			setElemReady( true )
		}
	}

	return (
		<Fragment>
			<h4>
				<Link to={ '/single/' + get( row, 'object_id', '' ) } ref={ inputReference } onClick={ indexingData }>
					<span>{ decodeEntities( row.title ) }</span>
					<small>{ row.page }</small>
				</Link>
			</h4>

			{ elemReady && createPortal(
				<Fragment>
					<IndexingDataFooter data={ row } onClick={ clickHandler } />
					<IndexingDataToggle data={ row } />
				</Fragment>,
				col
			) }
		</Fragment>
	)
}

export default IndexingRow
