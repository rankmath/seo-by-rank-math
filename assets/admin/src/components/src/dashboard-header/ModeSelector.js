/**
 * External dependencies
 */
import { includes } from 'lodash'

/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n'
import apiFetch from '@wordpress/api-fetch'

/**
 * Internal dependencies
 */
import Button from '../buttons/Button'

// Function to update the Mode.
const updateMode = ( mode ) => {
	apiFetch( {
		method: 'POST',
		path: '/rankmath/v1/updateMode',
		data: { mode },
	} )
		.then( ( response ) => {
			if ( ! response ) {
				/*eslint no-alert: 0*/
				window.alert( __( 'Something went wrong! Please try again.', 'rank-math' ) )
				return
			}

			window.location.reload()
		} )
}

export default ( { page } ) => {
	if ( ! includes( [ 'status', 'version_control', 'tools', 'import_export', 'modules', 'help' ], page ) ) {
		return false
	}

	const { isAdvancedMode } = rankMath

	return (
		<div className="rank-math-mode-selector">
			<Button
				variant="link"
				className={ ! isAdvancedMode ? 'is-active' : '' }
				onClick={ () => ( updateMode( 'easy' ) ) }
			>
				{ __( 'Easy Mode', 'rank-math' ) }
			</Button>
			&nbsp;
			<Button
				variant="link"
				className={ isAdvancedMode ? 'is-active' : '' }
				onClick={ () => ( updateMode( 'advanced' ) ) }
			>
				{ __( 'Advanced Mode', 'rank-math' ) }
			</Button>
		</div>
	)
}
