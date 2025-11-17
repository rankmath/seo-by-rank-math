/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState, useEffect } from '@wordpress/element'

export default () => {
	const [ width, setWidth ] = useState( 0 )

	useEffect( () => {
		const interval = setInterval( () => {
			setWidth( ( prevWidth ) => {
				if ( prevWidth >= 100 ) {
					clearInterval( interval )

					return prevWidth
				}

				return prevWidth + 1
			} )
		}, 30 )

		return () => clearInterval( interval )
	}, [] )

	return (
		<div className="progress-bar">
			<div className="progress" style={ { width: width + '%' } }></div>
			<div className="progress-text">
				<span>{ width }% </span>
				{ __( 'Complete', 'rank-math' ) }
			</div>
		</div>
	)
}
