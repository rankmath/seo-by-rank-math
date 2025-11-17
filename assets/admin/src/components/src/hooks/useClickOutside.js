/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element'

export default () => {
	const [ isOpen, setIsOpen ] = useState( false )
	const ref = useRef( null )

	useEffect( () => {
		const handleClickOutside = ( { target } ) => {
			if (
				target &&
				ref.current &&
				! ref.current.contains( target )
			) {
				setIsOpen( false )
			}
		}

		document.addEventListener( 'mousedown', handleClickOutside )

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside )
		}
	}, [] )

	return [ isOpen, setIsOpen, ref ]
}
