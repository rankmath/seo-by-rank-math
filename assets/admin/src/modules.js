/**
 * External Dependencies
 */
import { isNull } from 'lodash'

/**
 * WordPress Dependencies
 */
import domReady from '@wordpress/dom-ready'
import { createRoot } from '@wordpress/element'

/**
 * Internal dependencies
 */
import App from './dashboard/index'

domReady( () => {
	const modulesPage = document.getElementById( 'rank-math-dashboard-page' )
	if ( isNull( modulesPage ) ) {
		return
	}

	createRoot( modulesPage ).render( <App /> )
} )
