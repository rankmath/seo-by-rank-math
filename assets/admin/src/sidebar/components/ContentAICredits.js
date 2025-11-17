/**
 * External dependencies
 */
import classnames from 'classnames'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { useState } from '@wordpress/element'
import { Button } from '@wordpress/components'
import apiFetch from '@wordpress/api-fetch'

export default ( { callback } ) => {
	const [ loading, setLoading ] = useState( '' )
	const className = classnames( 'rank-math-tooltip rank-math-button update-credits', {
		loading,
	} )
	return (
		<Button
			className={ className }
			onClick={ () => {
				setLoading( true )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/ca/getCredits',
				} )
					.catch( ( error ) => {
						setLoading( '' )
						alert( error.message )
					} )
					.then( ( response ) => {
						if ( response.error ) {
							alert( response.error )
						} else {
							callback ( response )
							// updateData( 'credits', response )
						}
						setLoading( '' )
					} )
			} }
		>
			<i className="dashicons dashicons-image-rotate"></i>
			<span>{ __( 'Click to refresh the available credits.', 'rank-math' ) }</span>
		</Button>
	)
}
