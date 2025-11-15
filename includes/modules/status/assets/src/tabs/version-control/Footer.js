/**
 * WordPress Dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal Dependencies
 */
import { Button } from '@rank-math/components'

export default ( props ) => {
	const [ buttonText, setButtonText ] = useState( __( 'Save Changes', 'rank-math' ) )
	const [ isDisabled, setDisabled ] = useState( false )
	return (
		<footer>
			<Button
				type="submit"
				variant="primary"
				size="xlarge"
				disabled={ isDisabled }
				onClick={ () => {
					setButtonText( __( 'Saving…', 'rank-math' ) )
					setDisabled( true )

					apiFetch( {
						method: 'POST',
						path: '/rankmath/v1/status/updateViewData',
						data: {
							...props,
						},
					} )
						.catch( ( error ) => {
							console.error( error.message )
							setButtonText( __( 'Failed! Try again', 'rank-math' ) )
						} )
						.then( ( response ) => {
							if ( ! response ) {
								setButtonText( __( 'Failed! Try again', 'rank-math' ) )
								return
							}
							setButtonText( __( 'Saved', 'rank-math' ) )
						} )
						.finally( () => {
							setTimeout( () => {
								setDisabled( false )
								setButtonText( __( 'Save Changes', 'rank-math' ) )
							}, 1000 )
						} )
				} }
			>
				{ buttonText }
			</Button>
		</footer>
	)
}
