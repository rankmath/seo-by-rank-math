/**
 * WordPress dependencies
 */
import { useEffect, useRef, useState } from '@wordpress/element'

export default () => {
	const [ isMenuOpen, setIsMenuOpen ] = useState( false )
	const containerRef = useRef( null )

	useEffect( () => {
		const handleClickOutside = ( { target } ) => {
			if (
				target &&
				containerRef.current &&
				! containerRef.current.contains( target )
			) {
				setIsMenuOpen( false )
			}
		}

		document.addEventListener( 'mousedown', handleClickOutside )

		return () => {
			document.removeEventListener( 'mousedown', handleClickOutside )
		}
	}, [] )

	return [ isMenuOpen, setIsMenuOpen, containerRef ]
}
