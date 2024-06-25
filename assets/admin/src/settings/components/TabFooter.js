// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable import/no-unresolved */

/**
 * External dependencies
 */
import { isEmpty, includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'
import { useState, useEffect } from '@wordpress/element'

/**
 * Internal dependencies
 */
import Button from '@rank-math/components/buttons/Button'

/**
 * Get Reset Button Label based on the button state.
 *
 * @param {Object} discardButton Original discard button label
 * @param {string} label         Current button state.
 */
const getDiscardButtonLabel = ( discardButton, label ) => {
	if ( label === 'resetting' ) {
		return __( 'Resetting…', 'rank-math' )
	}

	if ( label === 'resetted' ) {
		return __( 'Resetted', 'rank-math' )
	}

	return discardButton.children
}

/**
 * Get Update Button Label based on the button state.
 *
 * @param {Object} updateButton Original update button label
 * @param {string} label        Current button state.
 */
const getUpdateButtonLabel = ( updateButton, label ) => {
	if ( label === 'updating' ) {
		return __( 'Updating…', 'rank-math' )
	}

	if ( label === 'updated' ) {
		return __( 'Updated', 'rank-math' )
	}

	return updateButton.children
}

const TabFooter = ( props ) => {
	const { settings, resetSettings, saveSettings, footer } = props
	const [ label, setLabel ] = useState( '' )

	useEffect( () => {
		if ( ! includes( [ 'updated', 'resetted' ], label ) ) {
			return
		}

		setTimeout( () => ( setLabel( '' ) ), 1000 )
	}, [ label ] )

	return (
		<footer className="form-footer rank-math-ui">
			<Button
				onClick={ () => {
					resetSettings( setLabel )
				} }
				{ ...footer.discardButton }
				children={ getDiscardButtonLabel( footer.discardButton, label ) }
			/>

			<Button
				variant="primary"
				onClick={ () => {
					saveSettings( setLabel )
				} }
				disabled={ isEmpty( settings ) }
				{ ...footer.applyButton }
				children={ getUpdateButtonLabel( footer.applyButton, label ) }
			/>
		</footer>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			settings: select( 'rank-math-settings' ).getdirtySettings(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { type, settings } = props
		return {
			saveSettings( setLabel ) {
				setLabel( 'updating' )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/updateSettings',
					data: {
						type,
						settings: settings[ type ],
					},
				} ).then( ( response ) => {
					if ( ! response ) {
						/*eslint no-alert: 0*/
						setLabel( '' )
						window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
						return
					}

					setLabel( 'updated' )

					dispatch( 'rank-math-settings' ).resetdirtySettings()
				} )
			},
			resetSettings( setLabel ) {
				setLabel( 'resetting' )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/resetSettings',
					data: { type },
				} ).then( ( response ) => {
					if ( ! response ) {
						/*eslint no-alert: 0*/
						setLabel( '' )
						window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
						return
					}

					setLabel( 'resetted' )
					window.location.reload()
				} )
			},
		}
	} )
)( TabFooter )
