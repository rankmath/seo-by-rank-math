/*!
 * Rank Math - Role Manager
 *
 * @version 1.0.55
 * @author  Rank Math
 */

/**
 * External Dependencies
 */
import jQuery from 'jquery'
import { isNull } from 'lodash'

/**
 * WordPress dependencies
 */
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import { getStore } from '@rank-math-settings/redux/store'
import App from './App'

jQuery( () => {
	const generalSettings = document.getElementById( 'rank-math-settings' )
	if ( isNull( generalSettings ) ) {
		return
	}

	getStore()

	createRoot( generalSettings ).render( <App /> )
} )
