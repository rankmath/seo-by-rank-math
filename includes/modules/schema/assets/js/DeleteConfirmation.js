/**
 * External dependencies
 */
import { useState, useRef, useEffect } from 'react'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { Button } from '@wordpress/components'

/**
 * Delete confirmation component.
 *
 * @param {Object} props This component's props.
 */
const DeleteConfirmation = ( { onClick, children, className = '' } ) => {
	const node = useRef()
	const [ clicked, setClicked ] = useState( false )

	const handleClickOutside = ( event ) => {
		if ( node.current.contains( event.target ) ) {
			return
		}
		setClicked( false )
	}

	useEffect( () => {
		if ( clicked ) {
			document.addEventListener( 'mousedown', handleClickOutside )
		} else {
			document.removeEventListener( 'mousedown', handleClickOutside )
		}

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside )
		}
	}, [ clicked ] )

	return (
		<div ref={ node } className="rank-math-inline-confirmation">
			{ ! clicked && children( setClicked ) }
			{ clicked && (
				<div className="rank-math-confirm-delete">
					<span>{ __( 'Delete?', 'rank-math' ) }</span>
					<Button
						isLink
						onClick={ () => {
							setClicked( false )
							onClick()
						} }
					>
						<span>{ __( 'Yes', 'rank-math' ) }</span>
					</Button>
					<Button
						isLink
						onClick={ () => setClicked( false ) }
					>
						<span>{ __( 'No', 'rank-math' ) }</span>
					</Button>
				</div>
			) }
		</div>
	)
}

export default DeleteConfirmation
