// eslint-disable-next-line eslint-comments/disable-enable-pair
/* eslint-disable import/no-unresolved */

/**
 * External dependencies
 */
import jQuery from 'jquery'
import { includes, reduce, has, forEach, isArray, isEqual, isUndefined, isEmpty } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import { compose } from '@wordpress/compose'
import { withSelect, withDispatch } from '@wordpress/data'
import apiFetch from '@wordpress/api-fetch'
import { useState, useEffect, useRef } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { Button } from '@rank-math/components'
import addNotice from '@helpers/addNotice'

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

const getSettingsData = ( type, settings, tabs, isReset = false, originalSettings = {} ) => {
	if ( includes( [ 'roleCapabilities', 'redirections' ], type ) ) {
		return {
			data: settings,
			fields: {},
			updated: [],
		}
	}

	const fieldTypes = {}
	const updated = []
	const settingsData = isUndefined( tabs ) ? {} : reduce( tabs, ( result, tab ) => {
		if ( ! isArray( tab.fields ) || tab.fields.length === 0 ) {
			return result
		}

		if ( tab.name === 'htaccess' ) {
			result.htaccess_allow_editing = settings.htaccess_allow_editing
			result.htaccess_content = settings.htaccess_content

			if ( ! isEqual( settings.htaccess_content, originalSettings.htaccess_content ) ) {
				updated.push( 'htaccess_content' )
			}

			return result
		}

		forEach( tab.fields, ( field ) => {
			const id = field.id
			if ( isReset ) {
				if ( has( field, 'default' ) ) {
					result[ id ] = field.default
					fieldTypes[ id ] = field.type
				}
			} else {
				const value = ( settings[ id ] === '' || isUndefined( settings[ id ] ) ) && has( field, 'default' ) ? field.default : settings[ id ]
				result[ id ] = value
				fieldTypes[ id ] = field.type

				// Pass Image id.
				if ( field.type === 'file' && ! isUndefined( settings[ id + '_id' ] ) ) {
					result[ id + '_id' ] = settings[ id + '_id' ]
					fieldTypes[ id ] = 'text'
				}

				if ( ! isEqual( value, originalSettings[ id ] ) ) {
					updated.push( id )
				}
			}
		} )

		return result
	}, {} )

	return {
		data: settingsData,
		fields: fieldTypes,
		updated,
	}
}

const TabFooter = ( props ) => {
	const [ label, setLabel ] = useState( '' )

	const { settings, data, resetSettings, saveSettings, footer } = props
	const { validate, afterSave, ...applyButton } = footer.applyButton

	const originalSettingsRef = useRef( null )
	if ( originalSettingsRef.current === null ) {
		originalSettingsRef.current = { ...data } // first render only
	}

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
					if (
						window.confirm(
							__( 'Are you sure? You want to reset settings.', 'rank-math' )
						)
					) {
						resetSettings( setLabel )
					}
				} }
				{ ...footer.discardButton }
				children={ getDiscardButtonLabel( footer.discardButton, label ) }
			/>

			<Button
				variant="primary"
				onClick={ () => {
					const isValid = validate ? validate() : true

					if ( isValid ) {
						saveSettings( setLabel, originalSettingsRef.current )
					}
				} }
				disabled={ isEmpty( settings ) }
				{ ...applyButton }
				children={ getUpdateButtonLabel( applyButton, label ) }
			/>
		</footer>
	)
}

export default compose(
	withSelect( ( select ) => {
		return {
			settings: select( 'rank-math-settings' ).getdirtySettings(),
			data: select( 'rank-math-settings' ).getData(),
		}
	} ),
	withDispatch( ( dispatch, props ) => {
		const { type, settings, tabs, footer: { applyButton } } = props
		return {
			saveSettings( setLabel, originalSettings ) {
				const settingsData = getSettingsData( type, settings, tabs, false, originalSettings )
				setLabel( 'updating' )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/updateSettings',
					data: {
						type,
						settings: settingsData.data,
						fieldTypes: settingsData.fields,
						updated: settingsData.updated,
					},
				} ).then( ( response ) => {
					setLabel( 'updated' )

					if ( ! response ) {
						/*eslint no-alert: 0*/
						setLabel( '' )
						window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
						return
					}

					if ( ! isUndefined( response.notifications ) && response.notifications.length ) {
						forEach( response.notifications, ( notification ) => {
							const isError = ! isEmpty( notification.error )
							const message = isError ? notification.error : notification.success
							addNotice(
								message,
								isError ? 'error' : 'success',
								jQuery( '.rank-math-breadcrumbs-wrap' ),
								false,
								'rank-math-settings-notice',
								true,
							)
						} )

						jQuery( 'html, body' ).animate( { scrollTop: 0 }, 'fast' )
						return
					}

					if ( response.error ) {
						addNotice(
							response.error,
							'error',
							jQuery( '.rank-math-breadcrumbs-wrap' ),
							false,
							'rank-math-settings-notice'
						)
						jQuery( 'html, body' ).animate( { scrollTop: 0 }, 'fast' )
						return
					}

					dispatch( 'rank-math-settings' ).updateData( response.settings ? response.settings : { ...settingsData.data } )

					if ( response.success ) {
						addNotice(
							response.success,
							'success',
							jQuery( '.rank-math-breadcrumbs-wrap' ),
							false,
							'rank-math-settings-notice'
						)
						jQuery( 'html, body' ).animate( { scrollTop: 0 }, 'fast' )
						return
					}

					if ( response && ! response.error && applyButton.afterSave ) {
						applyButton.afterSave()
					}

					setLabel( 'updated' )

					dispatch( 'rank-math-settings' ).resetdirtySettings()
				} )
			},
			resetSettings( setLabel ) {
				const settingsData = getSettingsData( type, settings, tabs, true )
				setLabel( 'resetting' )
				apiFetch( {
					method: 'POST',
					path: '/rankmath/v1/updateSettings',
					data: {
						type,
						settings: settingsData.data,
						fieldTypes: settingsData.fields,
						isReset: true,
					},
				} ).then( ( response ) => {
					if ( ! response ) {
						/*eslint no-alert: 0*/
						setLabel( '' )
						window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
						return
					}

					setLabel( 'resetted' )
					dispatch( 'rank-math-settings' ).updateData( response.settings ? response.settings : { ...settingsData.data } )
					dispatch( 'rank-math-settings' ).resetdirtySettings()
				} )
			},
		}
	} )
)( TabFooter )
