/**
 * WordPress dependencies
 */
import { useState } from '@wordpress/element'
import { Button, Icon } from '@wordpress/components'

/**
 * Internal dependencies
 */
import './scss/Rating.scss'

/**
 * Rating component.
 *
 * @param {Object}   props           Component props.
 * @param {number}   props.value     The ratings value.
 * @param {Function} props.onChange  Callback invoked to update the rating value.
 * @param {string}   props.className CSS class for additional styling.
 */
export default ( { value, onChange, className = '', ...additionalProps } ) => {
	const [ smileyState, setSmileyState ] = useState( { face: '', num: '' } )

	// Determine the face based on the star hovered over
	const handleMouseOver = ( index ) => {
		const num = index + 1

		if ( index < 2 ) {
			setSmileyState( { face: 'angry', num } )
		} else if ( index >= 2 && index < 4 ) {
			setSmileyState( { face: 'normal', num } )
		} else {
			setSmileyState( { face: 'happy', num } )
		}
	}

	// Reset the smileyState when the mouse leaves the smiley face
	const handleMouseOut = () => {
		setSmileyState( { face: '', num: '' } )
	}

	return (
		<div { ...additionalProps } className={ `rank-math-rating ${ className }` }>
			<div className="rank-math-rating__stars">
				{ Array.from( { length: 5 }, ( _, index ) => (
					<Button
						key={ index }
						onMouseOut={ handleMouseOut }
						onMouseOver={ () => handleMouseOver( index ) }
						onClick={ () => onChange( index + 1 ) }
						icon={ <Icon icon="star-filled" /> }
						className={ index < smileyState.num && 'highlighted' }
					/>
				) ) }
			</div>

			<div className="rank-math-rating__face">
				<div className={ `smiley ${ smileyState.face }` }>
					<div className="eyes">
						<span className="eye"></span>
						<span className="eye"></span>
					</div>
					<div className="mouth"></div>
				</div>
			</div>
		</div>
	)
}
