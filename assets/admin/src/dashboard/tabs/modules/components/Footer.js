/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'
import { useState } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { Tooltip, ToggleControl } from '@rank-math/components'
import reloadMenu from './reloadMenu'

const getSettings = ( module ) => {
	if ( ! module.settings || ! module.isActive || module.isDisabled ) {
		return null
	}

	return (
		<a href={ module.settings } className="module-settings button button-secondary">
			{ __( 'Settings', 'rank-math' ) }
		</a>
	)
}

const getToggle = ( module, setLoading ) => {
	return (
		<ToggleControl
			checked={ module.isActive }
			disabled={ module.disabled }
			onChange={ ( isChecked ) => {
				setLoading( module.id )

				apiFetch(
					{
						method: 'POST',
						path: '/rankmath/v1/saveModule',
						data: {
							module: module.id,
							state: isChecked ? 'on' : 'off',
						},
					}
				).then( ( response ) => {
					setLoading( '' )
					if ( ! response ) {
						// eslint-disable-next-line no-alert
						window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
						return
					}

					wp.data.dispatch( 'rank-math-settings' ).updateModules( module.id, isChecked )

					reloadMenu()
				} ).catch( ( error ) => {
					setLoading( '' )
					// eslint-disable-next-line no-alert
					window.alert( error.message )
				} )
			} }
		/>
	)
}

const getToggleContainer = ( module, loading, setLoading ) => {
	if ( loading === module.id ) {
		return <span className="input-loading"></span>
	}

	if ( module.disabled && module.disabled_text ) {
		return (
			<Tooltip text={ module.disabled_text } placement="left">
				{ getToggle( module, setLoading ) }
			</Tooltip>
		)
	}

	return getToggle( module, setLoading )
}

export default ( { module } ) => {
	const [ loading, setLoading ] = useState()

	return (
		<div className="status wp-clearfix">
			{ getSettings( module ) }
			<div className="toggle-container">
				{ getToggleContainer( module, loading, setLoading ) }
			</div>
		</div>
	)
}
